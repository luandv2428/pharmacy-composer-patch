<?php
namespace Eway\EwayRapid\Model;

use Magento\Payment\Gateway\ConfigInterface;

class ModuleHelper
{
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    public function __construct(ConfigInterface $config, \Magento\Customer\Model\CustomerRegistry $customerRegistry)
    {
        $this->config = $config;
        $this->customerRegistry = $customerRegistry;
    }

    public function isActive()
    {
        return (bool) $this->config->getValue('active');
    }

    public function isBlockSuspectedFraudCustomer()
    {
        return $this->isActive() && (bool) $this->config->getValue('block_fraud_customers');
    }

    public function blockCustomer($customer)
    {
        if ($customer = $this->getCustomer($customer)) {
            $customer->setData('ewayrapid_is_blocked', 1)->save();
        }
        return $this;
    }

    public function unblockCustomer($customer)
    {
        if ($customer = $this->getCustomer($customer)) {
            $customer->setData('ewayrapid_is_blocked', 0)->save();
        }
        return $this;
    }

    public function isCustomerBlocked($customer)
    {
        if (! $this->isBlockSuspectedFraudCustomer()) {
            return false;
        }

        $customer = $this->getCustomer($customer);
        return  $customer && (bool) $customer->getData('ewayrapid_is_blocked');
    }

    protected function getCustomer($customer) //@codingStandardsIgnoreLine
    {
        try {
            if (! $customer instanceof \Magento\Customer\Model\Customer) {
                $customer = $this->customerRegistry->retrieve($customer);
            }
        } catch (\Exception $e) {
            $customer = false;
        }

        return $customer;
    }
}
