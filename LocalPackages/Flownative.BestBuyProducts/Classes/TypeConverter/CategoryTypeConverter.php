<?php
namespace Flownative\BestBuyProducts\TypeConverter;

use Flownative\BestBuyProducts\Domain\Model\Category;
use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyProducts\Domain\Repository\CategoryRepository;
use Neos\Flow\Property\Exception;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;

/**
 *
 */
class CategoryTypeConverter extends AbstractTypeConverter
{
    /**
     * @Flow\Inject
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @Flow\Inject
     * @var \Flownative\BestBuyApi\Domain\Repository\CategoryRepository
     */
    protected $categoryApiRepository;

    /**
     * @var array
     */
    protected $sourceTypes = ['string', 'array'];

    /**
     * @var string
     */
    protected $targetType = Category::class;

    /**
     * @var integer
     */
    protected $priority = 2;

    /**
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return Category|mixed|Error|object|void
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $sourceWasString = false;
        if (!is_array($source)) {
            $source = ['id' => $source];
            $sourceWasString = true;
        }

        if (!isset($source['id'])) {
            throw new \InvalidArgumentException('No id given!', 1523085913832);
        }

        $category = $this->categoryRepository->findByIdentifier($source['id']);
        if ($category === null && $sourceWasString === false) {
            $category = new Category($source['id']);
            $category = $this->applyPropertiesToCategory($category, $source, $configuration);
        }

        if ($category === null) {
            $categoryData = $this->categoryApiRepository->findByIdentifier($source['id']);
            $category = new Category($categoryData['id']);
            $category = $this->applyPropertiesToCategory($category, $categoryData, $configuration);
        }

        return $category;
    }

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
     * @param Category $category
     * @param $source
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return Category
     */
    protected function applyPropertiesToCategory(Category $category, $source, PropertyMappingConfigurationInterface $configuration = null)
    {
        if (isset($source['name'])) {
            $category->setName($source['name']);
        }

        if (isset($source['path'])) {
            end($source['path']);
            $parent = prev($source['path']);
            if ($parent !== false) {
                $parentCategory = $this->convertFrom($parent['id'], Category::class, [], $configuration);
                if ($parentCategory !== null) {
                    $category->setParent($parentCategory);
                }
            }
        }

        return $category;
    }
}
