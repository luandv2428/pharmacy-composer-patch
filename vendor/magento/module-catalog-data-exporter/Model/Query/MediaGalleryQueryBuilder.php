<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Product media gallery query for catalog data exporter
 */
class MediaGalleryQueryBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $mediaGalleryAttributeId;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get query for provider
     *
     * @param int[] $productIds
     * @param string $storeViewCode
     *
     * @return Select
     */
    public function getQuery(array $productIds, string $storeViewCode) : Select
    {
        $connection = $this->resourceConnection->getConnection();
        $catalogProductTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $productEntityJoinField = $connection->getAutoIncrementField($catalogProductTable);

        return $connection->select()->from(
            [
                'main' => $this->resourceConnection->getTableName('catalog_product_entity_media_gallery'),
            ],
            ['mediaType' => 'media_type', 'file' => 'value']
        )->joinInner(
            [
                's' => $this->resourceConnection->getTableName('store'),
            ],
            $connection->quoteInto('s.code = ?', $storeViewCode),
            ['storeViewCode' => 's.code']
        )->joinInner(
            [
                'entity' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value_to_entity'
                ),
            ],
            'main.value_id = entity.value_id'
        )->joinLeft(
            [
                'value' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value'
                ),
            ],
            'main.value_id = value.value_id AND value.store_id = s.store_id',
            []
        )->joinLeft(
            [
                'default_value' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value'
                ),
            ],
            'main.value_id = default_value.value_id AND default_value.store_id = 0',
            []
        )->joinLeft(
            [
                'value_video' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value_video'
                ),
            ],
            'main.value_id = value_video.value_id AND value_video.store_id = s.store_id',
            []
        )->joinLeft(
            [
                'default_value_video' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value_video'
                ),
            ],
            'main.value_id = default_value_video.value_id AND default_value_video.store_id = 0',
            []
        )->joinInner(
            [
                'product_entity' => $catalogProductTable,
            ],
            \sprintf('product_entity.%1$s = entity.%1$s', $productEntityJoinField),
            ['productId' => 'product_entity.entity_id']
        )->columns([
            'label' => $connection->getIfNullSql('value.label', 'default_value.label'),
            'sortOrder' => $connection->getIfNullSql('value.position', 'default_value.position'),
            'videoProvider' => $connection->getIfNullSql('value_video.provider', 'default_value_video.provider'),
            'videoUrl' => $connection->getIfNullSql('value_video.url', 'default_value_video.url'),
            'videoTitle' => $connection->getIfNullSql('value_video.title', 'default_value_video.title'),
            'videoDescription' => $connection->getIfNullSql(
                'value_video.description',
                'default_value_video.description'
            ),
            'videoMetadata' => $connection->getIfNullSql('value_video.metadata', 'default_value_video.metadata'),
        ])->where(
            'main.attribute_id = ?',
            $this->getMediaGalleryAttributeId()
        )->where(
            'product_entity.entity_id IN (?)',
            $productIds
        );
    }

    /**
     * Get media gallery attribute id
     *
     * @return int
     */
    private function getMediaGalleryAttributeId() : int
    {
        if (null === $this->mediaGalleryAttributeId) {
            $connection = $this->resourceConnection->getConnection();

            $this->mediaGalleryAttributeId = (int)$connection->fetchOne(
                $connection->select()
                    ->from(['a' => $this->resourceConnection->getTableName('eav_attribute')], ['attribute_id'])
                    ->join(
                        ['t' => $this->resourceConnection->getTableName('eav_entity_type')],
                        't.entity_type_id = a.entity_type_id',
                        []
                    )
                    ->where('t.entity_table = ?', 'catalog_product_entity')
                    ->where('a.attribute_code = ?', 'media_gallery')
            );
        }

        return $this->mediaGalleryAttributeId;
    }
}
