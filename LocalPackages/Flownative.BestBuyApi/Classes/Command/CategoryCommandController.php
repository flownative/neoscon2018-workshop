<?php
namespace Flownative\BestBuyApi\Command;

use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyApi\Domain\Repository\CategoryRepository;
use Neos\Flow\Cli\CommandController;

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
     *
     */
    public function listAllCommand()
    {
        foreach ($this->categoryRepository->findAll() as $category) {
            $this->outputLine($category['id'] . ' - ' . $category['name']);
        }
    }

    /**
     * @param int $from
     * @param int $limit
     */
    public function listFromCommand($from, $limit = 50)
    {
        foreach ($this->categoryRepository->findWithLimitAndOffset($limit, $from) as $category) {
            $this->outputLine($category['id'] . ' - ' . $category['name']);
        }
    }
}
