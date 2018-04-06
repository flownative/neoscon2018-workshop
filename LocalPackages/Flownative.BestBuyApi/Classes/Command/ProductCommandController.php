<?php
namespace Flownative\BestBuyApi\Command;

use Neos\Flow\Annotations as Flow;
use Flownative\BestBuyApi\Domain\Repository\ProductRepository;
use Neos\Flow\Cli\CommandController;

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
     *
     */
    public function listAllCommand()
    {
        foreach ($this->productRepository->findAll() as $product) {
            $this->outputLine($product['sku'] . ' - ' . $product['name']);
        }
    }

    /**
     * @param int $from
     * @param int $limit
     */
    public function listFromCommand($from, $limit = 50)
    {
        foreach ($this->productRepository->findWithLimitAndOffset($limit, $from) as $product) {
            $this->outputLine($product['sku'] . ' - ' . $product['name']);
        }
    }

    /**
     * @param string $sku
     */
    public function showSingleBySkuCommand(string $sku)
    {
        $product = $this->productRepository->findByIdentifier($sku);
        print_r($product);
    }

    public function findByCategoryCommand(string $categoryId)
    {
        foreach ($this->productRepository->findByCategory($categoryId) as $product) {
            $this->outputLine($product['sku'] . ' - ' . $product['name']);
        }
    }
}
