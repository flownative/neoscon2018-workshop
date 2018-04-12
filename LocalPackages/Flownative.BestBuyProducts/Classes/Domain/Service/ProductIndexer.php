<?php
namespace Flownative\BestBuyProducts\Domain\Service;

use Flownative\BestBuyProducts\Domain\Model\Product;
use Flownative\BestBuyProducts\Domain\Repository\ProductRepository;
use Flowpack\ElasticSearch\Domain\Model\Document;
use Flowpack\ElasticSearch\Domain\Model\GenericType;
use Flowpack\ElasticSearch\Domain\Model\Mapping;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class ProductIndexer
{
    use IndexRotationTrait;

    protected $typeName = 'bestbuy:product';

    /**
     * @var string
     */
    protected $indexName = 'bestbuy-products';

    /**
     * @var string
     */
    protected $indexNamePostfix;

    /**
     * @Flow\Inject
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * ProductIndexer constructor.
     */
    public function __construct()
    {
        $this->indexNamePostfix = time();
    }

    /**
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function createProductIndex()
    {
        $index = $this->getIndex();
        $index->setSettingsKey($this->indexName);
        $index->create();
        $this->prepareProductType($index);
    }

    /**
     * @param Product $product
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function indexProduct(Product $product)
    {
        $index = $this->getIndex();
        $productType = new GenericType($index, $this->typeName);

        $document = new Document($productType, [
            '__identifier' => $product->getSku(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'regularPrice' => $product->getRegularPrice(),
            'shortDescription' => $product->getShortDescription(),
            'manufacturer' => $product->getManufacturer(),
            'image' => $product->getImage(),
            'color' => $product->getColor(),
            'type' => $product->getType(),
            'modelNumber' => $product->getModelNumber()
        ], $product->getSku());
        $document->store();
    }

    /**
     * @return GenericType
     */
    protected function prepareProductType(\Flowpack\ElasticSearch\Domain\Model\Index $index)
    {
        $productType = new GenericType($index, $this->typeName);
        $mapping = new Mapping($productType);

        foreach (['modelNumber', 'color', 'manufacturer', 'sku', 'category'] as $notAnalyzedProperty) {
            $mapping->setPropertyByPath($notAnalyzedProperty,
                [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                ]
            );
        }

        $mapping->setPropertyByPath('regularPrice', [
            'type' => 'integer',
            'index' => 'not_analyzed',
            'include_in_all' => false
        ]);

        $mapping->setPropertyByPath('image', [
            'type' => 'string',
            'index' => 'not_analyzed',
            'include_in_all' => false
        ]);

        $mapping->setPropertyByPath('shortDescription',
            [
                'type' => 'string',
                'analyzer' => 'custom_analyzer'
            ]
        );

        $mapping->apply();
        return $productType;
    }
}
