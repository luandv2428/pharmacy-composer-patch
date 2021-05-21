<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class ShippingAddressDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $order = $payment->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if (!$shippingAddress) {
            return [];
        }

        return [
            Config::SHIPPING_ADDRESS => [
                Config::FIRST_NAME  => $shippingAddress->getFirstname(),
                Config::LAST_NAME   => $shippingAddress->getLastname(),
                Config::STREET_1    => $shippingAddress->getStreetLine1(),
                Config::STREET_2    => $shippingAddress->getStreetLine2(),
                Config::CITY        => $shippingAddress->getCity(),
                Config::STATE       => $shippingAddress->getRegionCode(),
                Config::COUNTRY     => strtolower($shippingAddress->getCountryId()),
                Config::POSTAL_CODE => $shippingAddress->getPostcode(),
                Config::PHONE       => $shippingAddress->getTelephone(),
            ]
        ];
    }
}
