<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;

class SetDefault extends AbstractMyCards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $id = $this->getTokenId();
        $this->tokenManager->setDefaultToken($id);
        $this->messageManager->addSuccessMessage(__('Customer Credit Card has been saved successfully.'));
        return $this->success();
    }
}
