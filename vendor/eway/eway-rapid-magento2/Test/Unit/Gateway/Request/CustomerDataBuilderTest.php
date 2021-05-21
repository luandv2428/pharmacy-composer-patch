<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Gateway\Request\CustomerDataBuilder;
use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class CustomerDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildCustomerDataWithPrefix()
    {
        $builder = new CustomerDataBuilder();

        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
        $order->method('getOrderIncrementId')->willReturn('000123');
        $billingAddress = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class);
        $billingAddress->method('getPrefix')->willReturn('Ms.');
        $billingAddress->method('getFirstname')->willReturn('John');
        $billingAddress->method('getLastname')->willReturn('Doe');
        $billingAddress->method('getCompany')->willReturn('eWay');
        $billingAddress->method('getStreetLine1')->willReturn('street 1');
        $billingAddress->method('getStreetLine2')->willReturn('street 2');
        $billingAddress->method('getCity')->willReturn('Sydney');
        $billingAddress->method('getRegionCode')->willReturn('NSW');
        $billingAddress->method('getPostcode')->willReturn('1234');
        $billingAddress->method('getCountryId')->willReturn('AU');
        $billingAddress->method('getTelephone')->willReturn('1234567890');
        $billingAddress->method('getEmail')->willReturn('john.doe@eway.com.au');
        $order->expects($this->once())->method('getBillingAddress')->willReturn($billingAddress);
        $paymentDO->expects($this->once())->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDO];

        $result = $builder->build($buildSubject);

        $this->assertEquals(
            [
                Config::CUSTOMER => [
                    Config::REFERENCE    => '000123',
                    Config::TITLE        => 'Ms.',
                    Config::FIRST_NAME   => 'John',
                    Config::LAST_NAME    => 'Doe',
                    Config::COMPANY_NAME => 'eWay',
                    Config::STREET_1     => 'street 1',
                    Config::STREET_2     => 'street 2',
                    Config::CITY         => 'Sydney',
                    Config::STATE        => 'NSW',
                    Config::POSTAL_CODE  => '1234',
                    Config::COUNTRY      => 'au',
                    Config::PHONE        => '1234567890',
                    Config::EMAIL        => 'john.doe@eway.com.au',
                ]
            ],
            $result
        );
    }
}