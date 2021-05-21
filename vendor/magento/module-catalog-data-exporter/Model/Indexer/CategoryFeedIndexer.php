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
 * Category export feed indexer class
 */
class CategoryFeedIndexer extends FeedIndexer
{
    /**
     * Indexer identifier
     */
    const INDEXER_ID = 'catalog_data_exporter_categories';

    /**
     * @var CategoryIndexerCallbackInterface
     */
    private $indexerCallback;

    /**
     * @param Processor $processor
     * @param ResourceConnection $resourceConnection
     * @param DataSerializerInterface $serializer
     * @param FeedIndexMetadata $feedIndexMetadata
     * @param CategoryIndexerCallbackInterface $indexerCallback
     */
    public function __construct(
        Processor $processor,
        ResourceConnection $resourceConnection,
        DataSerializerInterface $serializer,
        FeedIndexMetadata $feedIndexMetadata,
        CategoryIndexerCallbackInterface $indexerCallback
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
        foreach ($ids as $categoryData) {
            $idsArray[] = $categoryData['categoryId'];
        }
        $this->indexerCallback->execute($idsArray);
    }
}
