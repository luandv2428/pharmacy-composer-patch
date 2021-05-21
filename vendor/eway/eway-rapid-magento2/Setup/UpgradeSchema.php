<?php
namespace Eway\EwayRapid\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $table = $setup->getConnection()->newTable($setup->getTable('eway_orders'))
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Order Id'
                )
                ->addColumn(
                    'items_ordered',
                    Table::TYPE_TEXT,
                    1024,
                    [],
                    'Items Ordered and Qty'
                )
                ->addColumn(
                    'beagle_score',
                    Table::TYPE_DECIMAL,
                    "5,2",
                    ['unsigned' => false],
                    'Beagle Score'
                )
                ->addColumn(
                    'fraud_action',
                    Table::TYPE_TEXT,
                    50,
                    [],
                    'Fraud Action'
                )
                ->addColumn(
                    'transaction_captured',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Transaction Captured status returned from gateway'
                )
                ->addColumn(
                    'fraud_messages',
                    Table::TYPE_TEXT,
                    1024,
                    [],
                    'Fraud codes and messages if have any'
                )
                ->addColumn(
                    'should_verify',
                    Table::TYPE_BOOLEAN,
                    null,
                    [],
                    'Should be verify with gateway for fraud status'
                )
                ->addForeignKey(
                    $setup->getFkName('eway_orders', 'order_id', 'sales_order_grid', 'entity_id'),
                    'order_id',
                    $setup->getTable('sales_order_grid'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName('eway_orders', 'should_verify'),
                    ['should_verify'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('eway_orders'),
                'transaction_id',
                ['TYPE' => Table::TYPE_TEXT, 'LENGTH' => 50, 'COMMENT' => 'Transaction Id']
            );
        }

        $setup->endSetup();
    }
}
