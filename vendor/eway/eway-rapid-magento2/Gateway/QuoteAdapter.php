<?php
namespace Eway\EwayRapid\Gateway;

use Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory;
use Magento\Quote\Api\Data\CartInterface;

class QuoteAdapter extends \Magento\Payment\Gateway\Data\Quote\QuoteAdapter
{
    /** @var \Magento\Quote\Model\Quote */
    protected $quote; //@codingStandardsIgnoreLine

    public function __construct(CartInterface $quote, AddressAdapterFactory $addressAdapterFactory)
    {
        parent::__construct($quote, $addressAdapterFactory);
        $this->quote = $quote;
    }

    public function getRemoteIp()
    {
        return $this->quote->getRemoteIp();
    }

    public function getShippingAmount()
    {
        return $this->getAddressModel()->getBaseShippingAmount();
    }

    public function getShippingTaxAmount()
    {
        return $this->getAddressModel()->getBaseShippingTaxAmount();
    }

    public function getShippingAmountInclTax()
    {
        return $this->getAddressModel()->getBaseShippingInclTax();
    }

    public function getDiscountAmount()
    {
        return $this->getAddressModel()->getBaseDiscountAmount();
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function getAddressModel() //@codingStandardsIgnoreLine
    {
        return $this->quote->isVirtual() ? $this->quote->getBillingAddress() : $this->quote->getShippingAddress();
    }

    public function getItems()
    {
        $items = parent::getItems();
        if (!$items) {
            $items = $this->quote->getAllItems();
        }

        return $items;
    }
}
