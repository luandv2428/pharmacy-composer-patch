<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataExporter\Model\Indexer;

use Magento\DataExporter\Export\Processor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * Product export feed indexer class
 */
class FeedIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FeedIndexMetadata
     */
    private $feedIndexMetadata;

    /**
     * @var DataSerializerInterface
     */
    private $dataSerializer;

    /**
     * @param Processor $processor
     * @param ResourceConnection $resourceConnection
     * @param DataSerializerInterface $serializer
     * @param FeedIndexMetadata $feedIndexMetadata
     */
    public function __construct(
        Processor $processor,
        ResourceConnection $resourceConnection,
        DataSerializerInterface $serializer,
        FeedIndexMetadata $feedIndexMetadata
    ) {
        $this->processor = $processor;
        $this->resourceConnection = $resourceConnection;
        $this->feedIndexMetadata = $feedIndexMetadata;
        $this->dataSerializer = $serializer;
    }

    /**
     * Get Ids select
     *
     * @param int $lastKnownId
     * @return Select
     */
    private function getIdsSelect(int $lastKnownId) : Select
    {
        $columnExpression = sprintf('s.%s', $this->feedIndexMetadata->getSourceTableField());
        $whereClause = sprintf('s.%s > ?', $this->feedIndexMetadata->getSourceTableField());
        $connection = $this->resourceConnection->getConnection();
        return $connection->select()
            ->from(
                ['s' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getSourceTableName())],
                [
                    $this->feedIndexMetadata->getFeedIdentity() =>
                        's.' . $this->feedIndexMetadata->getSourceTableField()
                ]
            )
            ->where($whereClause, $lastKnownId)
            ->order($columnExpression)
            ->limit($this->feedIndexMetadata->getBatchSize());
    }

    /**
     * Get all product IDs
     *
     * @return \Generator
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAllIds() : ?\Generator
    {
        $connection = $this->resourceConnection->getConnection();
        $lastKnownId = 0;
        $continueReindex = true;
        while ($continueReindex) {
            $ids = $connection->fetchAll($this->getIdsSelect((int)$lastKnownId));
            if (empty($ids)) {
                $continueReindex = false;
            } else {
                yield $ids;
                $lastKnownId = end($ids)[$this->feedIndexMetadata->getFeedIdentity()];
            }
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeFull()
    {
        $this->truncateFeedTable();
        foreach ($this->getAllIds() as $ids) {
            $this->markRemoved($ids);
            $this->process($ids);
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $arguments = [];
        foreach ($ids as $id) {
            $arguments[] = [$this->feedIndexMetadata->getFeedIdentity() => $id];
        }
        $this->markRemoved($ids);
        $this->process($arguments);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->markRemoved([$id]);
        $this->process([[$this->feedIndexMetadata->getFeedIdentity() => $id]]);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $arguments = [];
        foreach ($ids as $id) {
            $arguments[] = [$this->feedIndexMetadata->getFeedIdentity() => $id];
        }
        $this->markRemoved($ids);
        $this->process($arguments);
    }

    /**
     * Indexer feed data processor
     *
     * @param array $ids
     * @return void
     */
    protected function process($ids = []) : void
    {
        $data = $this->processor->process($this->feedIndexMetadata->getFeedName(), $ids);
        $chunks = array_chunk($data, $this->feedIndexMetadata->getBatchSize());
        $connection = $this->resourceConnection->getConnection();
        foreach ($chunks as $chunk) {
            $connection->insertOnDuplicate(
                $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName()),
                $this->dataSerializer->serialize($chunk),
                $this->feedIndexMetadata->getFeedTableMutableColumns()
            );
        }
    }

    /**
     * Mark field removed
     *
     * @param array $ids
     * @return void
     */
    private function markRemoved(array $ids) : void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->joinLeft(
                ['s' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getSourceTableName())],
                sprintf(
                    'f.%s = s.%s',
                    $this->feedIndexMetadata->getFeedTableField(),
                    $this->feedIndexMetadata->getSourceTableField()
                ),
                ['is_deleted' => new \Zend_Db_Expr('1')]
            )
            ->where(sprintf('s.%s IS NULL', $this->feedIndexMetadata->getSourceTableField()))
            ->where(
                sprintf('f.%s IN (?)', $this->feedIndexMetadata->getFeedTableField()),
                $ids
            );
        $update = $connection->updateFromSelect(
            $select,
            ['f' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName())]
        );
        $connection->query($update);
    }

    /**
     * Truncate feed table
     *
     * @return void
     */
    private function truncateFeedTable(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $feedTable = $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName());
        $connection->truncateTable($feedTable);
    }
}
