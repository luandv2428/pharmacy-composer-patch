<?php
namespace Eway\EwayRapid\Block\Adminhtml;

class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct() //@codingStandardsIgnoreLine
    {
        $this->_controller = 'ewayrapid_order';
        $this->_headerText = __('eWAY Orders');
        parent::_construct();
        $this->removeButton('add');
    }
}
