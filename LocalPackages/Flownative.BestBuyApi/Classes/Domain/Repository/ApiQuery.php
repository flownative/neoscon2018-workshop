<?php
namespace Flownative\BestBuyApi\Domain\Repository;

use Flownative\BestBuyApi\Domain\Utility\ApiHelper;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;

/**
 *
 */
class ApiQuery
{
    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var int
     */
    protected $limit = 10;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * ApiQuery constructor.
     *
     * @param string $apiKey
     * @param string $baseUri
     * @param string $query
     * @param array $parameters
     */
    public function __construct(string $apiKey, string $baseUri, string $type, string $query, array $parameters)
    {
        $this->baseUri = $baseUri;
        $this->type = $type;
        $this->query = $query;
        $this->parameters = array_merge(ApiHelper::getDefaultApiParameters($apiKey), $parameters);
    }

    /**
     * @return UriInterface
     */
    public function getQueryUri()
    {
        return new Uri($this->baseUri . $this->type . $this->query . '?' . http_build_query($this->parameters));
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * @return bool
     */
    public function hasDefinedOffset()
    {
        return ($this->getParameter('page') !== null);
    }
}
