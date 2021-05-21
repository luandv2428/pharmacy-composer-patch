<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;

class Delete extends AbstractMycards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $id = $this->getTokenId();
        $this->tokenManager->deleteToken($id);
        $this->messageManager->addSuccessMessage(__('Your Credit Card has been deleted successfully.'));
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
