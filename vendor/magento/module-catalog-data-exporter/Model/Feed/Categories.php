<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Feed;

use Magento\DataExporter\Model\FeedInterface;

/**
 * Categories feed class
 */
class Categories implements FeedInterface
{
    /**
     * @var GenericFeed
     */
    private $genericFeed;

    /**
     * @param GenericFeedFactory $genericFeedFactory
     */
    public function __construct(GenericFeedFactory $genericFeedFactory)
    {
        $this->genericFeed = $genericFeedFactory->create([
            'tableName' => 'catalog_data_exporter_categories'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getFeedSince(string $timestamp): array
    {
        return $this->genericFeed->getFeedSince($timestamp);
    }

    /**
     * @inheritDoc
     */
    public function getFeedByIds(array $ids, array $storeViewCodes = []): array
    {
        return $this->genericFeed->getFeedByIds($ids, $storeViewCodes);
    }
}
