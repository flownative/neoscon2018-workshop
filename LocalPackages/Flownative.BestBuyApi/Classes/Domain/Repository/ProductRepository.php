<?php
namespace Flownative\BestBuyApi\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\RepositoryInterface;

/**
 *
 */
class ProductRepository
{
    /**
     * @Flow\InjectConfiguration(path="apiKey", package="Flownative.BestBuyApi")
     * @var string
     */
    protected $apiKey;

    /**
     * @return ApiQueryResult|QueryResultInterface
     */
    public function findAll()
    {
        return new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'products', '', []));
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return ApiQueryResult
     */
    public function findWithLimitAndOffset(int $limit, int $offset = 0)
    {
        if (($offset % $limit) > 0) {
            throw new \InvalidArgumentException('The given limit and offset are not divisible', 1523014628061);
        }

        $page = ($offset / $limit) + 1;

        return new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'products', '', [
            'pageSize' => $limit,
            'page' => $page
        ]));
    }

    /**
     * @param string $identifier
     * @return mixed|object
     */
    public function findByIdentifier(string $identifier)
    {
        $apiResult = new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'products', sprintf('(sku=%s)', $identifier), []));
        return $apiResult->getFirst();
    }

    /**
     * @param string $categoryId
     * @return ApiQueryResult
     */
    public function findByCategory(string $categoryId)
    {
        return new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'products', sprintf('(categoryPath.id=%s)', $categoryId), []));
    }
}
