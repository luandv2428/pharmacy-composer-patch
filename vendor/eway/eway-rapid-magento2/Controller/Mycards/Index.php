<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;
use Magento\Framework\App\ResponseInterface;

class Index extends AbstractMycards
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @codingStandardsIgnoreFile
     */
    protected function _execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Credit Cards (eWAY)'));
        return $resultPage;
    }
}
