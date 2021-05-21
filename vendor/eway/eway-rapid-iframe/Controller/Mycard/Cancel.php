<?php
namespace Eway\IFrame\Controller\Mycard;

use Magento\Framework\App\ResponseInterface;

class Cancel extends \Magento\Framework\App\Action\Action
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setPath('*/mycards/index/');
    }
}
