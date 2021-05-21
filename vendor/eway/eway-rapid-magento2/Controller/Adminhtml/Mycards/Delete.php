<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;

class Delete extends AbstractMyCards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $id = $this->getTokenId();
        $this->tokenManager->deleteToken($id);
        $this->messageManager->addSuccessMessage(__('Customer Credit Card has been deleted successfully.'));
        return $this->success();
    }
}
