<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataExporter\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;

/**
 * Class responsible for providing feed data
 */
class Feed implements FeedInterface
{
    /**
     * Offset
     *
     * @var int
     */
    private static $offset = 1000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FeedIndexMetadata
     */
    private $feedIndexMetadata;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param FeedIndexMetadata $feedIndexMetadata
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        FeedIndexMetadata $feedIndexMetadata
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->feedIndexMetadata = $feedIndexMetadata;
    }

    /**
     * Get feed by timestamp
     *
     * @param string $timestamp
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getFeedSince(string $timestamp): array
    {
        $timestamp = ($timestamp === '1' ? (int)$timestamp : $timestamp);
        $connection = $this->resourceConnection->getConnection();

        $limit = $connection->fetchOne(
            $connection->select()
                ->from(
                    ['t' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName())],
                    [ 'modified_at']
                )
                ->where('t.modified_at > ?', $timestamp)
                ->order('modified_at')
                ->limit(1, self::$offset)
        );
        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName())],
                [
                    'feed_data',
                    'modified_at',
                    'is_deleted'
                ]
            )
            ->where('t.modified_at > ?', $timestamp);
        if ($limit) {
            $select->where('t.modified_at <= ?', $limit);
        }
        return $this->fetchData($select);
    }

    /**
     * Get feed data by IDs
     *
     * @param array $ids
     * @param array $storeViewCodes
     * @return array
     */
    public function getFeedByIds(array $ids, array $storeViewCodes = []) : array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName($this->feedIndexMetadata->getFeedTableName())],
                [
                    'feed_data',
                    'modified_at',
                    'is_deleted'
                ]
            )
            ->where('t.is_deleted = ?', 0)
            ->where(sprintf('t.%s IN (?)', $this->feedIndexMetadata->getFeedTableField()), $ids);

        if (!empty($storeViewCodes)) {
            $select->where('t.store_view_code IN (?)', $storeViewCodes);
        }

        return $this->fetchData($select);
    }

    /**
     * Fetch data from prepared select statement
     *
     * @param string|\Magento\Framework\DB\Select $select
     * @return array
     */
    private function fetchData($select): array
    {
        $connection = $this->resourceConnection->getConnection();
        $recentTimestamp = null;

        $cursor = $connection->query($select);
        $output = [];
        while ($row = $cursor->fetch()) {
            $dataRow = $this->serializer->unserialize($row['feed_data']);
            $dataRow['modifiedAt'] = $row['modified_at'];
            $dataRow['deleted'] = (bool) $row['is_deleted'];
            $output[] = $dataRow;
            if ($recentTimestamp == null || $recentTimestamp < $row['modified_at']) {
                $recentTimestamp = $row['modified_at'];
            }
        }
        return [
            'recentTimestamp' => $recentTimestamp,
            'feed' => $output
        ];
    }
}
