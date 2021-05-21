<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Feed;

use Magento\DataExporter\Model\FeedInterface;

/**
 * Product feed indexer
 */
class Products implements FeedInterface
{
    /**
     * @var GenericFeed
     */
    private $genericFeed;

    /**
     * @param GenericFeedFactory $genericFeedFactory
     */
    public function __construct(
        GenericFeedFactory $genericFeedFactory
    ) {
        $this->genericFeed = $genericFeedFactory->create([
            'tableName' => 'catalog_data_exporter_products'
        ]);
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
        return $this->genericFeed->getFeedSince($timestamp);
    }

    /**
     * Get deleted products by IDs
     *
     * @param string[] $ids
     * @return array
     */
    public function getDeletedByIds(array $ids)
    {
        return $this->genericFeed->getDeletedByIds($ids);
    }

    /**
     * Get product data by IDs
     *
     * @param array $ids
     * @param array $storeViewCodes
     * @return array
     */
    public function getFeedByIds(array $ids, array $storeViewCodes = []): array
    {
        return $this->genericFeed->getFeedByIds($ids, $storeViewCodes);
    }
}
