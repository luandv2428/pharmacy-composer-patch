<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCatalog\Model;

use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\FlagManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\SaaSCatalog\Cron\SubmitFeed;

/**
 * Manager for SaaS Catalog feed re-sync functions
 *
 * @api
 */
class ResyncManager
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SubmitFeed
     */
    private $submitFeed;

    /**
     * @var FeedIndexer
     */
    private $feedIndexer;

    /**
     * @var FeedInterface
     */
    private $feedInterface;

    /**
     * @var string
     */
    private $flagName;

    /**
     * @var string
     */
    private $indexerName;

    /**
     * @var string
     */
    private $registryTableName;

    /**
     * @param FeedIndexer $feedIndexer
     * @param FlagManager $flagManager
     * @param IndexerRegistry $indexerRegistry
     * @param SubmitFeed $submitFeed
     * @param ResourceConnection $resourceConnection
     * @param FeedInterface $feedInterface
     * @param string $flagName
     * @param string $indexerName
     * @param string $registryTableName
     */
    public function __construct(
        FeedIndexer $feedIndexer,
        FlagManager $flagManager,
        IndexerRegistry $indexerRegistry,
        SubmitFeed $submitFeed,
        ResourceConnection $resourceConnection,
        FeedInterface $feedInterface,
        string $flagName,
        string $indexerName,
        string $registryTableName
    ) {
        $this->feedIndexer = $feedIndexer;
        $this->flagManager = $flagManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->submitFeed = $submitFeed;
        $this->resourceConnection = $resourceConnection;
        $this->feedInterface = $feedInterface;
        $this->flagName = $flagName;
        $this->indexerName = $indexerName;
        $this->registryTableName = $registryTableName;
    }

    /**
     * Execute full SaaSCatalog feed data re-generate and re-submit
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     */
    public function executeFullResync(): void
    {
        $this->resetIndexedData();
        $this->resetSubmittedData();
        $this->regenerateFeedData();
        $this->submitAllToFeed();
    }

    /**
     * Execute SaaSCatalog feed data re-submit only
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeResubmitOnly(): void
    {
        $this->resetSubmittedData();
        $this->submitAllToFeed();
    }

    /**
     * Reset SaaSCatalog indexed feed data in order to re-generate
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetIndexedData(): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->invalidate();
    }

    /**
     * Reset SaaSCatalog submitted feed data in order to re-send
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetSubmittedData(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $registryTable = $this->resourceConnection->getTableName($this->registryTableName);
        $connection->truncateTable($registryTable);
        $this->flagManager->deleteFlag($this->flagName);
    }

    /**
     * Re-index SaaSCatalog feed data
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function regenerateFeedData(): void
    {
        $this->feedIndexer->executeFull();
    }

    /**
     * Submit all items to catalog feed
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     */
    public function submitAllToFeed(): void
    {
        $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
        $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ? $lastSyncTimestamp : '1');
        while ($data['recentTimestamp'] !== null) {
            $result = $this->submitFeed->submitFeed($data);
            if ($result) {
                $this->flagManager->saveFlag($this->flagName, $data['recentTimestamp']);
            }
            $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
            $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ? $lastSyncTimestamp : '1');
        }
    }
}
