<?php
namespace Eway\EwayRapid\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var bool
     */
    private $joinOrderTable;

    protected $_idFieldName = 'order_id'; //@codingStandardsIgnoreLine

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $joinOrderTable = false,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->joinOrderTable = $joinOrderTable;
    }

    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('Eway\EwayRapid\Model\Order', 'Eway\EwayRapid\Model\ResourceModel\Order');
    }

    protected function _renderFiltersBefore() //@codingStandardsIgnoreLine
    {
        parent::_renderFiltersBefore();

        if ($this->joinOrderTable) {
            $this->join(
                ['og' => 'sales_order_grid'],
                "main_table.order_id = og.entity_id",
                ['og.increment_id', 'og.created_at', 'og.billing_name', 'og.status']
            );
        }

        return $this;
    }

    public function filterOrdersThatNeedToBeVerified()
    {
        $this->addFieldToFilter('should_verify', 1);
        return $this;
    }
}
