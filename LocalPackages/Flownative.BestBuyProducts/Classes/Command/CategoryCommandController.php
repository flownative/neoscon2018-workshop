<?php
namespace Flownative\BestBuyProducts\Command;

use Flownative\BestBuyProducts\Domain\Model\Category;
use Flownative\BestBuyProducts\TypeConverter\CategoryTypeConverter;
use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyProducts\Domain\Repository\CategoryRepository;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Core\Booting\Scripts;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 *
 */
class CategoryCommandController extends CommandController
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
     * @Flow\InjectConfiguration(package="Neos.Flow")
     * @var array
     */
    protected $flowSettings;

    /**
     * A simple
     */
    public function simpleImportAllCommand()
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
        $this->outputLine(memory_get_peak_usage());
    }

    /**
     *
     */
    public function importWithSubExecCommand()
    {
        foreach ($this->categoryApiRepository->findAllIdentifiers() as $categoryData) {
            Scripts::executeCommand('import:importSingleCategory', $this->flowSettings, true, ['id' => $categoryData['id']]);
        }
        $this->outputLine(memory_get_peak_usage());
    }

    /**
     * @param string $id
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     * @internal
     */
    public function importSingleCategoryCommand(string $id)
    {
        $categoryTypeConverter = new CategoryTypeConverter();
        $category = $categoryTypeConverter->convertFrom($id, Category::class);
        if ($this->persistenceManager->isNewObject($category)) {
            $this->categoryRepository->add($category);
        } else {
            $this->categoryRepository->update($category);
        }
        $this->outputLine('Imported: ' . $category->getId());
        $this->outputLine('');
    }
}
