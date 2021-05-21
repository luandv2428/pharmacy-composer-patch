<?php
namespace Eway\EwayRapid\Controller;

use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Gateway\ConfigInterface;

abstract class AbstractMycards extends \Magento\Framework\App\Action\Action
{
    /** @codingStandardsIgnoreLine @var Session */
    protected $customerSession;

    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    /** @codingStandardsIgnoreLine @var PageFactory */
    protected $pageFactory;

    /** @codingStandardsIgnoreLine @var ManagerInterface */
    protected $tokenManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        ConfigInterface $config,
        PageFactory $pageFactory,
        ManagerInterface $tokenManager
    ) {
    
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->pageFactory = $pageFactory;
        $this->tokenManager = $tokenManager;
    }

    public function execute()
    {
        if (!$this->config->getValue('token_enabled')) {
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }

        try {
            return $this->_execute();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }

    // @codingStandardsIgnoreLine
    protected function getTokenId()
    {
        $id = $this->getRequest()->getParam('id');
        if (!is_numeric($id)) {
            throw new PaymentException(__('Invalid token id'));
        }

        return (int) $id;
    }

    // @codingStandardsIgnoreLine
    abstract protected function _execute();
}
