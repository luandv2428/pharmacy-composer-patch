<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Gateway\Request\CustomerTokenDataBuilder;
use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class CustomerTokenDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $tokenManager;

    /** @var CustomerTokenDataBuilder */
    private $customerTokenDataBuilder;

    protected function setUp()
    {
        $this->tokenManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->customerTokenDataBuilder = new CustomerTokenDataBuilder($this->tokenManager);
    }

    public function testBuildCustomerHaveTokenId()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
        $billingAddress = new DataObject([
            'prefix'          => 'Ms.',
            'firstname'       => 'John',
            'lastname'        => 'Doe',
            'company'         => 'eWay',
            'street_line_1'   => 'street 1',
            'street_line_2'   => 'street 2',
            'city'            => 'Sydney',
            'region_code'     => 'NSW',
            'postcode'        => '1234',
            'country_id'      => 'au',
            'telephone'       => '1234567890',
            'email'           => 'john.doe@eway.com.au',
            'job_description' => 'developer',
            'mobile'          => '9876543210',
            'fax'             => '1029387456'
        ]);
        $order->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($billingAddress);
        $paymentDO->expects($this->atLeastOnce())->method('getOrder')->willReturn($order);

        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->once())->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ID, 1]
        ]);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);

        $this->tokenManager->expects($this->once())->method('getCustomerTokenId')
            ->with($this->equalTo(1))->willReturn('realtokenid');

        $buildSubject = ['payment' => $paymentDO];

        $result = $this->customerTokenDataBuilder->build($buildSubject);

        $this->assertEquals(
            [
                Config::REFERENCE         => '',
                Config::TITLE             => 'Ms.',
                Config::FIRST_NAME        => 'John',
                Config::LAST_NAME         => 'Doe',
                Config::COMPANY_NAME      => 'eWay',
                Config::STREET_1          => 'street 1',
                Config::STREET_2          => 'street 2',
                Config::CITY              => 'Sydney',
                Config::STATE             => 'NSW',
                Config::POSTAL_CODE       => '1234',
                Config::COUNTRY           => 'au',
                Config::PHONE             => '1234567890',
                Config::EMAIL             => 'john.doe@eway.com.au',
                Config::TOKEN_CUSTOMER_ID => 'realtokenid',
                Config::JOB_DESCRIPTION   => 'developer',
                Config::MOBILE            => '9876543210',
                Config::FAX               => '1029387456'
            ],
            $result
        );
    }

    public function testBuildCustomerNoTokenId()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
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
        $order->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($billingAddress);
        $paymentDO->expects($this->atLeastOnce())->method('getOrder')->willReturn($order);

        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->once())->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ID, null]
        ]);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);

        $this->tokenManager->expects($this->never())->method('getCustomerTokenId');

        $buildSubject = ['payment' => $paymentDO];

        $result = $this->customerTokenDataBuilder->build($buildSubject);

        $this->assertEquals(
            [
                Config::REFERENCE         => '',
                Config::TITLE             => 'Ms.',
                Config::FIRST_NAME        => 'John',
                Config::LAST_NAME         => 'Doe',
                Config::COMPANY_NAME      => 'eWay',
                Config::STREET_1          => 'street 1',
                Config::STREET_2          => 'street 2',
                Config::CITY              => 'Sydney',
                Config::STATE             => 'NSW',
                Config::POSTAL_CODE       => '1234',
                Config::COUNTRY           => 'au',
                Config::PHONE             => '1234567890',
                Config::EMAIL             => 'john.doe@eway.com.au',
            ],
            $result
        );
    }
}