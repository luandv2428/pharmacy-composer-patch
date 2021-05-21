<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Unblock extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;

    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->moduleHelper = $moduleHelper;
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        try {
            if ($customerId) {
                $customer = $this->customerRegistry->retrieve($customerId);
                $this->moduleHelper->unblockCustomer($customer);
                $this->getMessageManager()->addSuccessMessage(__('Customer has been unblocked successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            'customer/index/edit',
            ['id' => $customerId]
        );
    }
}
