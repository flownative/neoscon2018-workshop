<?php
namespace Flownative\BestBuyApi\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\RepositoryInterface;

/**
 * @Flow\Scope("singleton")
 */
class CategoryRepository implements RepositoryInterface
{
    /**
     * @Flow\InjectConfiguration(path="apiKey", package="Flownative.BestBuyApi")
     * @var string
     */
    protected $apiKey;

    public function getEntityClassName()
    {
        // TODO: Implement getEntityClassName() method.
    }

    public function add($object)
    {
        // TODO: Implement add() method.
    }

    public function remove($object)
    {
        // TODO: Implement remove() method.
    }

    /**
     * @return ApiQueryResult|QueryResultInterface
     */
    public function findAll()
    {
        return new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'categories', '(id=abcat*)', []));
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
        return new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'categories', '(id=abcat*)', [
            'pageSize' => $limit,
            'page' => $page
        ]));
    }

    /**
     * @param mixed $identifier
     * @return mixed|object
     */
    public function findByIdentifier($identifier)
    {
        $apiResult = new ApiQueryResult(new ApiQuery($this->apiKey, 'https://api.bestbuy.com/v1/', 'categories', '(id=' . $identifier . ')', []));
        return $apiResult->getFirst();
    }

    public function createQuery()
    {
        // TODO: Implement createQuery() method.
    }

    public function countAll()
    {
        $allResult = $this->findAll();
        return $allResult->count();
    }

    public function removeAll()
    {
        // TODO: Implement removeAll() method.
    }

    public function setDefaultOrderings(array $defaultOrderings)
    {
        // TODO: Implement setDefaultOrderings() method.
    }

    public function update($object)
    {
        // TODO: Implement update() method.
    }

    public function __call($method, $arguments)
    {
        // TODO: Implement __call() method.
    }

}
