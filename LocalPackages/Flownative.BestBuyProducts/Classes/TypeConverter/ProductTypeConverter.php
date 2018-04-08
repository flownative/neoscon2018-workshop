<?php
namespace Flownative\BestBuyProducts\TypeConverter;

use Flownative\BestBuyProducts\Domain\Model\Product;
use Flownative\BestBuyProducts\Domain\Repository\ProductRepository;
use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;

/**
 *
 */
class ProductTypeConverter extends AbstractTypeConverter
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
     * @var array
     */
    protected $sourceTypes = ['string', 'array'];

    /**
     * @var string
     */
    protected $targetType = Product::class;

    /**
     * @var integer
     */
    protected $priority = 2;

    /**
     * Convert no properties in the source array
     *
     * @param mixed $source
     * @return array
     */
    public function getSourceChildPropertiesToBeConverted($source)
    {
        return [];
    }

    /**
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return Product|mixed|Error|object
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $sourceWasString = false;
        if (!is_array($source)) {
            $source = ['sku' => $source];
            $sourceWasString = true;
        }

        if (!isset($source['sku'])) {
            throw new \InvalidArgumentException('No sku given!', 1523185647798);
        }

        $product = $this->productRepository->findByIdentifier($source['sku']);
        if ($product === null && $sourceWasString === false) {
            $product = new Product($source['sku']);
            $product = $this->applyProperties($product, $source, $configuration);
        }

        if ($product === null) {
            $data = $this->productApiRepository->findByIdentifier($source['sku']);
            $product = new Product($source['sku']);
            $product = $this->applyProperties($product, $data, $configuration);
        }

        return $product;
    }

    /**
     * @param Product $product
     * @param $source
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return Product
     */
    protected function applyProperties(Product $product, $source, PropertyMappingConfigurationInterface $configuration = null)
    {
        if (isset($source['name'])) {
            $product->setName($source['name']);
        }

        if (isset($source['shortDescription'])) {
            $product->setShortDescription($source['shortDescription']);
        }

        if (isset($source['color'])) {
            $product->setColor($source['color']);
        }

        if (isset($source['image'])) {
            $product->setImage($source['image']);
        }

        if (isset($source['type'])) {
            $product->setType($source['type']);
        }

        if (isset($source['manufacturer'])) {
            $product->setManufacturer($source['manufacturer']);
        }

        if (isset($source['modelNumber'])) {
            $product->setModelNumber($source['modelNumber']);
        }

        if (isset($source['active'])) {
            $product->setActive($source['active']);
        }

        if (isset($source['regularPrice'])) {
            $product->setRegularPrice(intval($source['regularPrice'] * 100));
        }

        return $product;
    }
}
