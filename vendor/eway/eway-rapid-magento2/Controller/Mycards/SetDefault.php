<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;

class SetDefault extends AbstractMycards
{
    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $id = $this->getTokenId();
        $this->tokenManager->setDefaultToken($id);
        $this->messageManager->addSuccessMessage(__('Your Credit Card has been saved successfully.'));
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
