<?php
namespace Eway\EwayRapid\Model\Customer\Adminhtml;

use Eway\EwayRapid\Model\Customer\ProviderInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;

class Provider implements ProviderInterface
{
    /** @var ObjectManagerInterface */
    protected $objectManager; // @codingStandardsIgnoreLine

    protected $currentCustomer = null; // @codingStandardsIgnoreLine

    /** @var RequestInterface */
    protected $request; // @codingStandardsIgnoreLine

    /** @var \Magento\Framework\Registry */
    protected $registry; // @codingStandardsIgnoreLine
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->sessionQuote = $sessionQuote;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return Customer
     */
    public function getCurrentCustomer()
    {
        if ($this->currentCustomer == null) {
            $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

            if (!$customerId) {
                $customerId = $this->request->getParam('customer_id');
            }

            if (!$customerId) {
                $customerId = $this->sessionQuote->getCustomerId();
            }

            if (!$customerId) {
                $customerId = $this->sessionQuote->getQuote()->getCustomerId();
            }

            if ($customerId) {
                $this->currentCustomer = $this->customerFactory->create()->load($customerId);
            }
        }

        return $this->currentCustomer;
    }
}
