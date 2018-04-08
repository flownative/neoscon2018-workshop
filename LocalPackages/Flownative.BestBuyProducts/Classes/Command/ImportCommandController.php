<?php
namespace Flownative\BestBuyProducts\Command;

use Flownative\BestBuyProducts\Domain\Model\Category;
use Flownative\BestBuyProducts\TypeConverter\CategoryTypeConverter;
use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyProducts\Domain\Repository\CategoryRepository;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 *
 */
class ImportCommandController extends CommandController
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
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * A simple
     */
    public function simpleImportAllCategoriesCommand()
    {
        $categoryTypeConverter = new CategoryTypeConverter();

        foreach ($this->categoryApiRepository->findAll() as $categoryData) {
            $category = $categoryTypeConverter->convertFrom($categoryData, Category::class);
            if ($category === null) {
                $this->outputLine('Could not create category: ' . $categoryData['id']);
                continue;
            }
            if ($this->persistenceManager->isNewObject($category)) {
                $this->categoryRepository->add($category);
            } else {
                $this->categoryRepository->update($category);
            }
            $this->outputLine('Imported: ' . $category->getId());
        }
    }
}
