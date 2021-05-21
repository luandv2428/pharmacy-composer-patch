<?php
namespace Eway\EwayRapid\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_isPkAutoIncrement = false; // @codingStandardsIgnoreLine

    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('eway_orders', 'order_id');
    }
}
