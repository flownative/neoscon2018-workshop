<?php
namespace Flownative\BestBuyProducts\Domain\Repository;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;

/**
 * @Flow\Scope("singleton")
 */
class ProductRepository extends Repository
{
    /**
     * @return IterableResult
     */
    public function findAllIterator()
    {
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->createQueryBuilder();

        return $queryBuilder
            ->select('product')
            ->from($this->getEntityClassName(), 'product')
            ->getQuery()->iterate();
    }

    /**
     * Iterate over an IterableResult and return a Generator
     *
     * This method is useful for batch processing huge result set as it clears the object
     * manager and detaches the current object on each iteration.
     *
     * @param IterableResult $iterator
     * @param callable $callback
     * @return \Generator
     */
    public function iterate(IterableResult $iterator, callable $callback = null)
    {
        $iteration = 0;
        foreach ($iterator as $object) {
            $object = current($object);
            yield $object;
            if ($callback !== null) {
                call_user_func($callback, $iteration, $object);
            }
            $iteration++;
        }
    }
}
