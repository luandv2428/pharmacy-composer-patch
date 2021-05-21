<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;

class Create extends AbstractMycards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $this->_forward('edit');
    }
}
