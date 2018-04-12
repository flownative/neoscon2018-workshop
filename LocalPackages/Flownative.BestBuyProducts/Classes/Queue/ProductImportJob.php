<?php
namespace Flownative\BestBuyProducts\Queue;

use Flownative\BestBuyProducts\Domain\Model\Category;
use Flownative\BestBuyProducts\Domain\Model\Product;
use Flownative\BestBuyProducts\Domain\Repository\ProductRepository;
use Flownative\BestBuyProducts\TypeConverter\ProductTypeConverter;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
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
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contextFactory;

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
        $category = $this->persistenceManager->getObjectByIdentifier($this->categoryIdentifier, Category::class, true);
        $productConverter = new ProductTypeConverter();
        foreach ($this->productData as $productData) {
            $product = $productConverter->convertFrom($productData, Product::class, []);
            $productNode = $this->createProductNode($product);
            $product->setCategory($category);
            if ($this->persistenceManager->isNewObject($product)) {
                $this->productRepository->add($product);
            } else {
                $this->productRepository->update($product);
            }
        }

        return true;
    }

    /**
     * @param Product $product
     * @return \Neos\ContentRepository\Domain\Model\Node
     * @throws \Neos\ContentRepository\Exception\NodeExistsException
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    protected function createProductNode(Product $product)
    {
        $context = $this->contextFactory->create();
        $productRoot = $context->getNode('/sites/neosdemo/the-book');
        $productNodeName = 'product' . $product->getSku();
        $productNode = $productRoot->getNode($productNodeName);
        if ($productNode !== null) {
            return $productNode;
        }

        $productNodeType = $this->nodeTypeManager->getNodeType('Flownative.BestBuyProducts:Product');
        $productNode = $productRoot->createNode($productNodeName, $productNodeType);
        $productNode->setProperty('product', $product);
        return $productNode;

    }

    public function getLabel()
    {
        return 'ProductImport Job';
    }
}
