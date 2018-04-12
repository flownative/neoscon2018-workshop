<?php
namespace Flownative\BestBuyProducts\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Domain\Model\Client;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Utility\Arrays;

/**
 *
 */
class ProductQueryBuilder implements ProtectedContextAwareInterface, \JsonSerializable
{
    /**
     * @Flow\Inject
     * @var Client
     */
    protected $elasticSearchClient;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $from;

    /**
     * The Elasticsearch request, as it is being built up.
     *
     * @var array
     */
    protected $request = [
        'query' => [
        'filtered' => [
            'query' => ['bool' => ['must' => [['match_all' => []]]]],
            'filter' => ['bool' => ['must' => [], 'should' => []]]
        ]
    ]];

    /**
     * @var string
     */
    protected $indexName = 'bestbuy-products';

    /**
     * @param int $size
     * @return ProductQueryBuilder
     */
    public function size(int $size)
    {
        $this->request['size'] = (integer)$size;
        return $this;
    }

    /**
     * @param int $size
     * @return ProductQueryBuilder
     */
    public function from(int $size)
    {
        $this->request['from'] = (integer)$size;
        return $this;
    }

    /**
     * add an exact-match query for a given property
     *
     * @param string $propertyName Name of the property
     * @param mixed $value Value for comparison
     * @return ProductQueryBuilder
     * @api
     */
    public function exactMatch($propertyName, $value)
    {
        if ($value instanceof NodeInterface) {
            $value = $value->getIdentifier();
        }

        return $this->queryFilter('term', [$propertyName => $value]);
    }

    /**
     * add a range filter (gt) for the given property
     *
     * @param string $propertyName Name of the property
     * @param mixed $value Value for comparison
     * @return ProductQueryBuilder
     * @api
     */
    public function greaterThan($propertyName, $value)
    {
        return $this->queryFilter('range', [$propertyName => ['gt' => $value]]);
    }

    /**
     * add a range filter (gte) for the given property
     *
     * @param string $propertyName Name of the property
     * @param mixed $value Value for comparison
     * @return ProductQueryBuilder
     * @api
     */
    public function greaterThanOrEqual($propertyName, $value)
    {
        return $this->queryFilter('range', [$propertyName => ['gte' => $value]]);
    }

    /**
     * add a range filter (lt) for the given property
     *
     * @param string $propertyName Name of the property
     * @param mixed $value Value for comparison
     * @return ProductQueryBuilder
     * @api
     */
    public function lessThan($propertyName, $value)
    {
        return $this->queryFilter('range', [$propertyName => ['lt' => $value]]);
    }

    /**
     * add a range filter (lte) for the given property
     *
     * @param string $propertyName Name of the property
     * @param mixed $value Value for comparison
     * @return ProductQueryBuilder
     */
    public function lessThanOrEqual($propertyName, $value)
    {
        return $this->queryFilter('range', [$propertyName => ['lte' => $value]]);
    }

    /**
     * @param string $searchString
     * @return QueryInterface
     */
    public function fulltext(string $searchString)
    {
        $this->appendAtPath('query.filtered.query.bool.must', ['match_phrase' => ['shortDescription' => ['query' => $searchString, 'analyzer' => 'custom_analyzer']]]);
        return $this;
    }

    /**
     * @param string $filterType
     * @param $filterOptions
     * @param string $clauseType
     * @return ProductQueryBuilder
     */
    public function queryFilter(string $filterType, $filterOptions, string $clauseType = 'must')
    {
        if (!in_array($clauseType, ['must', 'should', 'must_not'])) {
            throw new \InvalidArgumentException('The given clause type "' . $clauseType . '" is not supported. Must be one of "must", "should", "must_not".', 1383716082);
        }

        $this->appendAtPath('query.filtered.filter.bool.' . $clauseType, [$filterType => $filterOptions]);
        return $this;
    }

    /**
     * Modify a part of the Elasticsearch Request denoted by $path, merging together
     * the existing values and the passed-in values.
     *
     * @param string $path
     * @param mixed $requestPart
     * @return QueryInterface
     */
    public function setByPath($path, $requestPart)
    {
        $valueAtPath = Arrays::getValueByPath($this->request, $path);
        if (is_array($valueAtPath)) {
            $result = Arrays::arrayMergeRecursiveOverrule($valueAtPath, $requestPart);
        } else {
            $result = $requestPart;
        }

        $this->request = Arrays::setValueByPath($this->request, $path, $result);

        return $this;
    }

    /**
     * @return string
     */
    public function toArray()
    {
        return $this->prepareRequest();
    }

    /**
     * @return string
     */
    public function getRequestAsJson()
    {
        return json_encode($this);
    }

    /**
     * Set sorting
     *
     * @param array $configuration
     * @return ProductQueryBuilder
     */
    public function sort(array $configuration)
    {
        if (!isset($this->request['sort'])) {
            $this->request['sort'] = [];
        }
        $this->request['sort'][] = $configuration;

        return $this;
    }

    /**
     * This method adds a field based aggregation configuration. This can be used for simple
     * aggregations like terms
     *
     * Example Usage to create a terms aggregation for a property color:
     * nodes = ${Search....fieldBasedAggregation("colors", "color").execute()}
     *
     * Access all aggregation data with {nodes.aggregations} in your fluid template
     *
     * @param string $name The name to identify the resulting aggregation
     * @param string $field The field to aggregate by
     * @param string $type Aggregation type
     * @param string $parentPath
     * @param int $size The amount of buckets to return
     * @return $this
     */
    public function fieldBasedAggregation(string $name, string $field, string $type = 'terms', string $parentPath = '', int $size = 10)
    {
        $aggregationDefinition = [
            $type => [
                'field' => $field,
                'size' => $size
            ]
        ];

        $this->aggregation($name, $aggregationDefinition, $parentPath);

        return $this;
    }

    /**
     * @param string $name
     * @param array $aggregationDefinition
     * @param string $parentPath
     * @return ProductQueryBuilder
     */
    public function aggregation(string $name, array $aggregationDefinition, $parentPath = '')
    {
        if (!array_key_exists('aggregations', $this->request)) {
            $this->request['aggregations'] = [];
        }

        if ((string)$parentPath !== '') {
            $this->addSubAggregation($parentPath, $name, $aggregationDefinition);
        } else {
            $this->request['aggregations'][$name] = $aggregationDefinition;
        }

        return $this;
    }

    /**
     * This is an low level method for internal usage.
     *
     * You can add a custom $aggregationConfiguration under a given $parentPath. The $parentPath foo.bar would
     * insert your $aggregationConfiguration under
     * $this->request['aggregations']['foo']['aggregations']['bar']['aggregations'][$name]
     *
     * @param string $parentPath The parent path to add the sub aggregation to
     * @param string $name The name to identify the resulting aggregation
     * @param array $aggregationConfiguration
     * @return ProductQueryBuilder
     */
    protected function addSubAggregation(string $parentPath, string $name, array $aggregationConfiguration)
    {
        // Find the parentPath
        $path =& $this->request['aggregations'];

        foreach (explode('.', $parentPath) as $subPart) {
            if ($path == null || !array_key_exists($subPart, $path)) {
                throw new \InvalidArgumentException(sprintf('The parent path segment "%s" could not be found when adding a sub aggregation to parent path "%s"', $subPart, $parentPath));
            }
            $path =& $path[$subPart]['aggregations'];
        }

        $path[$name] = $aggregationConfiguration;

        return $this;
    }

    /**
     * @param string $name
     * @param array $suggestionDefinition
     * @return ProductQueryBuilder
     */
    public function suggestions(string $name, array $suggestionDefinition)
    {
        if (!array_key_exists('suggest', $this->request)) {
            $this->request['suggest'] = [];
        }

        $this->request['suggest'][$name] = $suggestionDefinition;
        return $this;
    }

    /**
     * @param $fragmentSize
     * @param null $fragmentCount
     * @return ProductQueryBuilder
     */
    public function highlight($fragmentSize, $fragmentCount = null)
    {
        if ($fragmentSize === false) {
            // Highlighting is disabled.
            unset($this->request['highlight']);
        } else {
            $this->request['highlight'] = [
                'fields' => [
                    '__fulltext*' => [
                        'fragment_size' => $fragmentSize,
                        'no_match_size' => $fragmentSize,
                        'number_of_fragments' => $fragmentCount
                    ]
                ]
            ];
        }
        return $this;
    }

    /**
     * @param $path
     * @param mixed $value
     */
    public function setValueByPath($path, $value)
    {
        $this->request = Arrays::setValueByPath($this->request, $path, $value);
    }

    /**
     * @param string $path
     * @param array $data
     */
    public function appendAtPath(string $path, array $data)
    {
        $currentElement =& $this->request;
        foreach (explode('.', $path) as $pathPart) {
            if (!isset($currentElement[$pathPart])) {
                throw new \InvalidArgumentException('The element at path "' . $path . '" was not an array (failed at "' . $pathPart . '").', 1383716367);
            }
            $currentElement =& $currentElement[$pathPart];
        }
        $currentElement[] = $data;
    }

    /**
     * Execute the query and return the raw result.
     *
     * @return array
     */
    public function fetch()
    {
        $request = $this->getRequestAsJson();
        $response = $this->elasticSearchClient->findIndex($this->indexName)->request('GET', '/_search', [], $request);

        return $response->getTreatedContent();
    }

    /**
     * Prepare the final request array
     *
     * This method is useful if you extend the current query implementation.
     *
     * @return array
     */
    protected function prepareRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed|void
     */
    public function jsonSerialize()
    {
        return $this->prepareRequest();
    }


    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
