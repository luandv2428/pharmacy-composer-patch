<?php
namespace Eway\EwayRapid\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class UnblockButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        CustomerRegistry $customerRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper
    ) {
        parent::__construct($context, $registry);
        $this->customerRegistry = $customerRegistry;
        $this->messageManager = $messageManager;
        $this->moduleHelper = $moduleHelper;
    }

    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId && $this->moduleHelper->isBlockSuspectedFraudCustomer()) {
            $customer = $this->customerRegistry->retrieve($customerId);
            if ($this->moduleHelper->isCustomerBlocked($customer)) {
                $this->messageManager->addWarningMessage(
                    __('This customer is currently being blocked for placing a suspected fraud order.
                To unblock, please click \'Unblock\' button above')
                );

                $data = [
                    'label' => __('Unblock'),
                    'class' => 'primary',
                    'on_click' => sprintf("location.href = '%s';", $this->getUnblockUrl()),
                    'sort_order' => 50,
                ];
            }
        }
        return $data;
    }

    protected function getUnblockUrl() //@codingStandardsIgnoreLine
    {
        return $this->getUrl('ewayrapid/customer/unblock', ['customer_id' => $this->getCustomerId()]);
    }
}
