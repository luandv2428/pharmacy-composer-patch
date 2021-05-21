<?php
namespace Eway\EwayRapid\Model\Customer;

use Magento\Customer\Model\Customer;

interface ProviderInterface
{
    /**
     * @return Customer
     */
    public function getCurrentCustomer();
}
