<?php
namespace Eway\EwayRapid\Plugin;

use Magento\Framework\Controller\ResultFactory;

class CheckoutBlockPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession, //@codingStandardsIgnoreLine
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory
    ) {
        $this->customerSession = $customerSession;
        $this->moduleHelper = $moduleHelper;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(\Magento\Checkout\Controller\Index\Index $subject, \Closure $proceed) //@codingStandardsIgnoreLine
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            if ($this->moduleHelper->isCustomerBlocked($customer)) {
                $this->messageManager->addErrorMessage(__('Your latest payment is being reviewed and 
                        you cannot place a new order temporarily. Please try again later.'));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }
        }

        return $proceed();
    }
}
