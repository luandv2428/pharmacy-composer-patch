<?php
namespace Eway\EwayRapid\Observer;

class FraudDetected implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;

    public function __construct(\Eway\EwayRapid\Model\ModuleHelper $moduleHelper)
    {
        $this->moduleHelper = $moduleHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->moduleHelper->isBlockSuspectedFraudCustomer()) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $observer->getEvent()->getData('payment');
            if (! $payment->getOrder()->getCustomerIsGuest()) {
                $this->moduleHelper->blockCustomer($payment->getOrder()->getCustomerId());
            }
        }
    }
}
