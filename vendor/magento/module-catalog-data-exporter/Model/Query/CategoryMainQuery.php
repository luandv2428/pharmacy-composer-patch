<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Store\Model\Store;

/**
 * Base category data query for category data exporter
 */
class CategoryMainQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $mainTable;

    /**
     * @param ResourceConnection $resourceConnection
     * @param string $mainTable
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        string $mainTable
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mainTable = $mainTable;
    }

    /**
     * Get query for provider
     *
     * @param array $arguments
     *
     * @return Select
     */
    public function getQuery(array $arguments) : Select
    {
        return $this->resourceConnection->getConnection()->select()
            ->from(
                ['main_table' => $this->resourceConnection->getTableName($this->mainTable)],
                [
                    'categoryId' => 'main_table.entity_id',
                    'createdAt' => 'main_table.created_at',
                    'updatedAt' => 'main_table.updated_at',
                    'level' => 'main_table.level',
                    'position' => 'main_table.position',
                    'parentId' => 'main_table.parent_id',
                    'path' => 'main_table.path',
                ]
            )
            ->joinCross(
                ['s' => $this->resourceConnection->getTableName('store')],
                ['storeViewCode' => 's.code']
            )
            ->join(
                ['sg' => $this->resourceConnection->getTableName('store_group')],
                's.group_id = sg.group_id',
                []
            )
            ->where('s.store_id != ?', Store::DEFAULT_STORE_ID)
            ->where('main_table.entity_id IN (?)', $arguments['categoryId'])
            ->where(
                \sprintf(
                    'main_table.path LIKE %s or main_table.path LIKE %s',
                    new Expression("CONCAT('%/', sg.root_category_id, '/%')"),
                    new Expression("CONCAT('%/', sg.root_category_id)")
                )
            );
    }
}
