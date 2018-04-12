<?php
namespace Flownative\BestBuyProducts\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Flownative\BestBuyProducts\Domain\Model\Category;
use Flownative\BestBuyProducts\Domain\Model\Product;
use Flownative\BestBuyProducts\Domain\Service\ProductIndexer;
use Flownative\BestBuyProducts\Domain\Service\ProductQueryBuilder;
use Flownative\BestBuyProducts\Queue\ProductImportJob;
use Flownative\BestBuyProducts\TypeConverter\ProductTypeConverter;
use Flowpack\JobQueue\Common\Job\JobManager;
use Flowpack\JobQueue\Common\Job\StaticMethodCallJob;
use Flowpack\JobQueue\Common\Queue\QueueManager;
use Neos\Eel\CompilingEvaluator;
use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyProducts\Domain\Repository\ProductRepository;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Core\Booting\Scripts;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 *
 */
class ProductCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @Flow\Inject
     * @var \Flownative\BestBuyApi\Domain\Repository\ProductRepository
     */
    protected $productApiRepository;

    /**
     * @Flow\Inject
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Doctrines EntityManager
     *
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var JobManager
     * @Flow\Inject
     */
    protected $jobManager;

    /**
     * @var QueueManager
     * @Flow\Inject
     */
    protected $queueManager;

    /**
     * @Flow\Inject(lazy=false)
     * @var CompilingEvaluator
     */
    protected $eelEvaluator;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow")
     * @var array
     */
    protected $flowSettings;

    /**
     * @param string $categoryIdentifier
     */
    public function importAllSimpleCommand(string $categoryIdentifier)
    {
        $category = $this->persistenceManager->getObjectByIdentifier($categoryIdentifier, Category::class, true);
        $productConverter = new ProductTypeConverter();
        foreach ($this->productApiRepository->findByCategory($categoryIdentifier) as $productData) {
            $product = $productConverter->convertFrom($productData, Product::class, []);
            $product->setCategory($category);
            if ($this->persistenceManager->isNewObject($product)) {
                $this->productRepository->add($product);
            } else {
                $this->productRepository->update($product);
            }
            $this->outputLine('Imported product: ' . $product->getSku());
        }
        $this->outputLine(memory_get_peak_usage());
    }

    /**
     * @param string $categoryIdentifier
     * @param int $jobSize
     */
    public function buildImportQueueCommand(string $categoryIdentifier, int $jobSize = 10)
    {
        $i = 0;
        $jobData = [];
        foreach ($this->productApiRepository->findByCategory($categoryIdentifier, $jobSize) as $productData) {
            $jobData[] = $productData;
            $i++;
            if ($i === 10) {
                $i = 0;
                $job = new ProductImportJob($categoryIdentifier, $jobData);
                $this->jobManager->queue('product-import', $job);
                $jobData = [];
            }
        }
        $this->outputLine(memory_get_peak_usage());
    }

    /**
     * @param int $exitAfter (time in seconds)
     * @param int $limit (number of jobs)
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    public function workImportQueueCommand($exitAfter = null, $limit = null)
    {
        $startTime = time();
        $timeout = null;
        $numberOfJobExecutions = 0;

        do {
            $this->outputLine(memory_get_peak_usage());
            $message = null;
            if ($exitAfter !== null) {
                $timeout = max(1, $exitAfter - (time() - $startTime));
            }

            try {
                $message = $this->jobManager->waitAndExecute('product-import', $timeout);
            } catch (JobQueueException $exception) {
                $numberOfJobExecutions++;
                $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
            } catch (\Exception $exception) {
                $this->outputLine('<error>Unexpected exception during job execution: %s, aborting...</error>', [$exception->getMessage()]);
                $this->quit(1);
            }

            if ($message !== null) {
                $numberOfJobExecutions++;
            }
            if ($exitAfter !== null && (time() - $startTime) >= $exitAfter) {
                $this->outputLine('Quitting after %d seconds due to <i>--exit-after</i> flag', [time() - $startTime]);
                $this->quit();
            }
            if ($limit !== null && $numberOfJobExecutions >= $limit) {
                $this->outputLine('Quitting after %d executed job%s due to <i>--limit</i> flag', [
                    $numberOfJobExecutions,
                    $numberOfJobExecutions > 1 ? 's' : ''
                ]);
                $this->quit();
            }
        } while (true);
    }

    /**
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function indexProductsCommand()
    {
        $callback = function ($iteration) {
            if ($iteration % 100) {
                $this->entityManager->clear();
            }
        };

        $this->productIndexer->createProductIndex();
        $iteratableResult = $this->productRepository->findAllIterator();
        foreach ($this->productRepository->iterate($iteratableResult, $callback) as $product) {
            $this->productIndexer->indexProduct($product);
            $this->outputLine('Indexed ' . $product->getSku());
        }

        $this->productIndexer->updateIndexAlias();
    }

    /**
     * @param string $eelExpression
     */
    public function queryIndexCommand(string $eelExpression)
    {
        $result = \Neos\Eel\Utility::evaluateEelExpression($eelExpression, $this->eelEvaluator, ['query' => new ProductQueryBuilder()], []);
        var_dump($result);
    }
}
