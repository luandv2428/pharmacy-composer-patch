<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;

class Create extends AbstractMyCards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $this->_forward('edit');
    }
}
