<?php
namespace Flownative\BestBuyProducts\Queue;

use Flownative\BestBuyProducts\Domain\Repository\ProductRepository;
use Flownative\BestBuyProducts\TypeConverter\ProductTypeConverter;
use Neos\Flow\Annotations as Flow;
use Flowpack\JobQueue\Common\Job\JobInterface;
use Flowpack\JobQueue\Common\Queue\Message;
use Flowpack\JobQueue\Common\Queue\QueueInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 *
 */
class ProductImportJob implements JobInterface
{
    /**
     * @Flow\Inject
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var string
     */
    protected $categoryIdentifier;

    /**
     * @var array
     */
    protected $productData = [];

    /**
     * ProductImportJob constructor.
     *
     * @param string $categoryIdentifier
     * @param array $productData
     */
    public function __construct(string $categoryIdentifier, array $productData = [])
    {
        $this->categoryIdentifier = $categoryIdentifier;
        $this->productData = $productData;
    }

    /**
     * @param QueueInterface $queue
     * @param Message $message
     * @return bool|void
     */
    public function execute(QueueInterface $queue, Message $message)
    {
        $productConverter = new ProductTypeConverter();
        foreach ($this->productData as $productData) {
            $product = $productConverter->convertFrom($productData, Product::class, []);
            if ($this->persistenceManager->isNewObject($product)) {
                $this->productRepository->add($product);
            } else {
                $this->productRepository->update($product);
            }
        }

        return true;
    }

    public function getLabel()
    {
        return 'ProductImport Job';
    }
}
