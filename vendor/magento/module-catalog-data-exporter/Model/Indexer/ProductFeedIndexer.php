<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Indexer;

use Magento\DataExporter\Export\Processor;
use Magento\DataExporter\Model\Indexer\DataSerializerInterface;
use Magento\DataExporter\Model\Indexer\FeedIndexer;
use Magento\DataExporter\Model\Indexer\FeedIndexMetadata;
use Magento\Framework\App\ResourceConnection;

/**
 * Product export feed indexer class
 */
class ProductFeedIndexer extends FeedIndexer
{
    /**
     * @var ProductIndexerCallbackInterface
     */
    private $indexerCallback;

    /**
     * @param Processor $processor
     * @param ResourceConnection $resourceConnection
     * @param DataSerializerInterface $serializer
     * @param FeedIndexMetadata $feedIndexMetadata
     * @param ProductIndexerCallbackInterface $indexerCallback
     */
    public function __construct(
        Processor $processor,
        ResourceConnection $resourceConnection,
        DataSerializerInterface $serializer,
        FeedIndexMetadata $feedIndexMetadata,
        ProductIndexerCallbackInterface $indexerCallback
    ) {

        parent::__construct($processor, $resourceConnection, $serializer, $feedIndexMetadata);
        $this->indexerCallback = $indexerCallback;
    }

    /**
     * Invokes indexation logic
     *
     * @param array $ids
     *
     * TODO: publishing logic should be changed, and decoupled from indexers,
     * TODO: publisher should support all entities,
     * TODO: after refactoring this class should be removed and method should change visibility back to private.
     */
    protected function process($ids = []): void
    {
        parent::process($ids);
        $idsArray = [];
        foreach ($ids as $productData) {
            $idsArray[] = $productData['productId'];
        }
        $this->indexerCallback->execute($idsArray);
    }
}
