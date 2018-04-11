<?php
namespace Flownative\BestBuyProducts\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Transfer\Exception\ApiException;

/**
 * Helper trait for rotating elastic search indices.
 * Expects implementing class to have a "indexName" and "indexNamePostfix" property
 */
trait IndexRotationTrait
{
    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
     */
    protected $_clientFactory;

    /**
     * Update the index alias
     *
     * @return void
     * @throws \Exception
     */
    public function updateIndexAlias()
    {
        $aliasName = $this->indexName; // The alias name is the unprefixed index name
        if ($this->getIndexName() === $aliasName) {
            throw new \Exception('UpdateIndexAlias is only allowed to be called when $this->setIndexNamePostfix has been created.', 1516800016285);
        }

        if (!$this->getIndex()->exists()) {
            throw new \Exception('The target index for updateIndexAlias does not exist. This shall never happen.', 1516800021360);
        }

        $aliasActions = [];
        try {
            $indexNames = $this->indexesByAlias($aliasName);
            if ($indexNames === []) {
                // if there is an actual index with the name we want to use as alias, remove it now
                $this->deleteIndex($aliasName);
            } else {
                foreach ($indexNames as $indexName) {
                    $aliasActions[] = [
                        'remove' => [
                            'index' => $indexName,
                            'alias' => $aliasName
                        ]
                    ];
                }
            }
        } catch (ApiException $exception) {
            // in case of 404, do not throw an error...
            if ($exception->getResponse()->getStatusCode() !== 404) {
                throw $exception;
            }
        }

        $aliasActions[] = [
            'add' => [
                'index' => $this->getIndexName(),
                'alias' => $aliasName
            ]
        ];

        $this->aliasActions($aliasActions);
    }

    /**
     * Returns list of aliases for given index name.
     *
     * @param string $alias
     * @return array
     * @throws ApiException
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function indexesByAlias($alias)
    {
        $client = $this->_clientFactory->create();
        try {
            $response = $client->request('GET', '/_alias/' . $alias);
        } catch (ApiException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                throw $exception;
            }

            return [];
        }

        // return empty array if content from response cannot be read as an array
        $treatedContent = $response->getTreatedContent();

        return is_array($treatedContent) ? array_keys($treatedContent) : [];
    }

    /**
     * Apply set of actions to aliases.
     *
     * @param array $actions
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function aliasActions(array $actions)
    {
        $client = $this->_clientFactory->create();
        $client->request('POST', '/_aliases', [], \json_encode(['actions' => $actions]));
    }

    /**
     * Delete the index given by name.
     *
     * @param string $index
     * @throws \Exception
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function deleteIndex($index)
    {
        $client = $this->_clientFactory->create();
        $response = $client->request('HEAD', '/' . $index);
        if ($response->getStatusCode() === 200) {
            $response = $client->request('DELETE', '/' . $index);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('The index "' . $index . '" could not be deleted. (return code: ' . $response->getStatusCode() . ')', 1395419177);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function elasticStatus()
    {
        $client = $this->_clientFactory->create();

        return $client->request('GET', '/_stats')->getTreatedContent();
    }

    /**
     * Remove old indices which are not active anymore (remember, each bulk index creates a new index from scratch,
     * making the "old" index a stale one).
     *
     * @return string[] a list of index names which were removed
     * @throws ApiException
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function removeOldIndices()
    {
        $aliasName = $this->indexName; // The alias name is the unprefixed index name

        $currentlyLiveIndices = $this->indexesByAlias($aliasName);

        $indexStatus = $this->elasticStatus();
        $allIndices = array_keys($indexStatus['indices']);

        $indicesToBeRemoved = [];

        foreach ($allIndices as $indexName) {
            if (strpos($indexName, $aliasName . '-') !== 0) {
                // filter out all indices not starting with the alias-name, as they are unrelated to our application
                continue;
            }

            if (array_search($indexName, $currentlyLiveIndices) !== false) {
                // skip the currently live index names from deletion
                continue;
            }

            $indicesToBeRemoved[] = $indexName;
        }

        array_walk($indicesToBeRemoved, function ($index) {
            $this->deleteIndex($index);
        });

        return $indicesToBeRemoved;
    }

    /**
     * @param float $allowedDeviation
     * @return bool
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function isIndexSizeHealthy(float $allowedDeviation = 0.1)
    {
        $newIndex = $this->getIndex();
        $newIndexDocStats = $newIndex->request('/_stats/docs')->getTreatedContent();
        $newDocCount = $newIndexDocStats['_all']['total']['docs'] ?? 0;

        $client = $this->_clientFactory->create();
        $mainIndex = $client->findIndex($this->indexName);
        if (!$mainIndex->exists()) {
            return true;
        }
        $mainIndexDocStats = $mainIndex->request('/_stats/docs')->getTreatedContent();
        $mainIndexDocCount = $mainIndexDocStats['_all']['total']['docs'] ?? 0;

        if ($newDocCount + ($mainIndexDocCount * $allowedDeviation) < $mainIndexDocCount) {
            return false;
        }

        return true;
    }

    /**
     * Get the index to be used.
     *
     * @return \Flowpack\ElasticSearch\Domain\Model\Index
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function getIndex()
    {
        $client = $this->_clientFactory->create();

        return $client->findIndex($this->getIndexName());
    }

    /**
     * Get the actual index name used (with postfix)
     *
     * @return string
     */
    protected function getIndexName()
    {
        return $this->indexName . '-' . $this->indexNamePostfix;
    }
}
