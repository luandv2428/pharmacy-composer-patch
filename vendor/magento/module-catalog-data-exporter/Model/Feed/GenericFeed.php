<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Feed;

use Magento\DataExporter\Model\FeedInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Generic feed provider class
 */
class GenericFeed implements FeedInterface
{
    /**
     * Offset
     *
     * @var int
     */
    private const OFFSET = 1000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param string $tableName
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        string $tableName
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->tableName = $tableName;
    }

    /**
     * @inheritDoc
     */
    public function getFeedSince(string $timestamp): array
    {
        $timestamp = ($timestamp === '1' ? (int)$timestamp : $timestamp);
        $connection = $this->resourceConnection->getConnection();

        $limit = $connection->fetchOne(
            $connection->select()
                ->from(
                    ['t' => $this->resourceConnection->getTableName($this->tableName)],
                    [ 'modified_at']
                )
                ->where('t.modified_at > ?', $timestamp)
                ->order('modified_at')
                ->limit(1, self::OFFSET)
        );
        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName($this->tableName)],
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
     * @inheritDoc
     */
    public function getFeedByIds(array $ids, array $storeViewCodes = []): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName($this->tableName)],
                [
                    'feed_data',
                    'modified_at',
                    'is_deleted'
                ]
            )
            ->where('t.is_deleted = ?', 0)
            ->where('t.id IN (?)', $ids);

        if (!empty($storeViewCodes)) {
            $select->where('t.store_view_code IN (?)', $storeViewCodes);
        }

        return $this->fetchData($select);
    }

    /**
     * Get entities by IDs
     *
     * @todo make this method generic for deletion both products and categories
     * @see https://github.com/magento/catalog-storefront/issues/182
     * @param string[] $ids
     * @return array
     */
    public function getDeletedByIds(array $ids)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName($this->tableName)],
                [
                    'feed_data',
                ]
            )
            ->where('t.is_deleted = ?', 1)
            ->where('t.id IN (?)', $ids);

        $connection = $this->resourceConnection->getConnection();
        $cursor = $connection->query($select);

        $output = [];
        while ($row = $cursor->fetch()) {
            $dataRow = $this->serializer->unserialize($row['feed_data']);
            $output[] = [
                'product_id' => $dataRow['productId'],
                'store_view_code' => $dataRow['storeViewCode'],
            ];
        }

        return $output;
    }

    /**
     * Fetch data from prepared select statement
     *
     * @param Select $select
     *
     * @return array
     */
    private function fetchData(Select $select): array
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
            'feed' => $output,
        ];
    }
}
