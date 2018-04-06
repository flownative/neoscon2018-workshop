<?php
namespace Flownative\BestBuyApi\Domain\Repository;

use Flownative\BestBuyApi\Domain\Model\Category;
use GuzzleHttp\Client;
use Psr\Http\Message\UriInterface;

/**
 *
 */
class ApiQueryResult implements \Iterator, \Countable
{
    /**
     * @var ApiQuery
     */
    protected $query;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var \Generator
     */
    protected $generator;

    /**
     * @var int
     */
    protected $count;

    /**
     * ApiQueryResult constructor.
     *
     * @param ApiQuery $query
     * @param Client $client
     */
    public function __construct(ApiQuery $query, Client $client = null)
    {
        $this->httpClient = $client ?? new Client();
        $this->query = $query;
        $this->prepareGenerator($query);
    }

    /**
     * @return Category
     */
    public function current()
    {
        return $this->generator->current();
    }

    /**
     *
     */
    public function next()
    {
        $this->generator->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->generator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->generator->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->generator->rewind();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return ApiQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed|object
     */
    public function getFirst()
    {
        $this->generator->rewind();
        return $this->generator->current();
    }

    public function toArray()
    {
        iterator_to_array($this->generator);
    }

    /**
     * @param ApiQuery $query
     */
    protected function prepareGenerator(ApiQuery $query)
    {
        $uri = $query->getQueryUri();
        $apiResult = $this->httpClient->get($uri);
        $result = \GuzzleHttp\json_decode($apiResult->getBody(), true);
        $this->count = $result['total'];
        $this->generator = $this->getGenerator($uri, $query->getType(), $query->hasDefinedOffset(), $result);
    }

    /**
     * @param UriInterface $uri
     * @param string $collectionKey
     * @param bool $hasDefinedOffest
     * @param array $initialResult
     * @return \Generator
     */
    protected function getGenerator(UriInterface $uri, string $collectionKey, bool $hasDefinedOffest, array $initialResult)
    {
        $originalQueryString = $uri->getQuery();
        if ($hasDefinedOffest) {
            yield from $this->iterateResult($initialResult, $collectionKey);
            return;
        }

        for ($i = 1; $i <= $initialResult['totalPages']; $i++) {
            $result = $initialResult;
            if ($i !== 1) {
                $modifiedUri = $uri->withQuery($originalQueryString . '&page=' . $i);
                $apiResult  = $this->httpClient->get($modifiedUri);
                $result = \GuzzleHttp\json_decode($apiResult->getBody(), true);
            }

            yield from $this->iterateResult($result, $collectionKey);
        }
    }

    protected function iterateResult(array $result, $collectionKey)
    {
        foreach ($result[$collectionKey] as $data) {
            yield $data;
        }
    }
}
