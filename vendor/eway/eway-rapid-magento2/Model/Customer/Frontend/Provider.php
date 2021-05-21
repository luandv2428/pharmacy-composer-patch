<?php
namespace Eway\EwayRapid\Model\Customer\Frontend;

use Eway\EwayRapid\Model\Customer\ProviderInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;

class Provider implements ProviderInterface
{
    /** @codingStandardsIgnoreLine @var Session */
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    public function getCurrentCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer();
        } else {
            return null;
        }
    }
}
