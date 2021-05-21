<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class CustomerDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $order = $payment->getOrder();
        $billingAddress = $order->getBillingAddress();

        return [
            Config::CUSTOMER => [
                Config::REFERENCE    => $order->getOrderIncrementId(),
                Config::TITLE        => $billingAddress->getPrefix() ? $billingAddress->getPrefix() : 'Mr.',
                Config::FIRST_NAME   => $billingAddress->getFirstname(),
                Config::LAST_NAME    => $billingAddress->getLastname(),
                Config::COMPANY_NAME => $billingAddress->getCompany(),
                Config::STREET_1     => $billingAddress->getStreetLine1(),
                Config::STREET_2     => $billingAddress->getStreetLine2(),
                Config::CITY         => $billingAddress->getCity(),
                Config::STATE        => $billingAddress->getRegionCode(),
                Config::POSTAL_CODE  => $billingAddress->getPostcode(),
                Config::COUNTRY      => strtolower($billingAddress->getCountryId()),
                Config::PHONE        => $billingAddress->getTelephone(),
                Config::EMAIL        => $billingAddress->getEmail(),
            ]
        ];
    }
}
