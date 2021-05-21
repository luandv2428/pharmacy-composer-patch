<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;

class Index extends AbstractMyCards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
