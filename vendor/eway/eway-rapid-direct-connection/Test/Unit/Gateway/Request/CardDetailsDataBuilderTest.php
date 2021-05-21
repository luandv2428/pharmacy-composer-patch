<?php
namespace Eway\DirectConnection\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Model\Config;

class CardDetailsDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSecureCardData()
    {
        $paymentDO = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockForAbstractClass(\Magento\Payment\Model\InfoInterface::class);

        $paymentDO->method('getPayment')->willReturn($paymentModel);
        $paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::SECURED_CARD_DATA, 'abc123']
        ]);

        $buildSubject = ['payment' => $paymentDO];
        $builder = new \Eway\DirectConnection\Gateway\Request\CardDetailsDataBuilder();
        $result = $builder->build($buildSubject);
        $this->assertEquals([
            Config::SECURED_CARD_DATA => 'abc123'
        ], $result);
    }

    public function testCardDetails()
    {
        $paymentDO = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockForAbstractClass(\Magento\Payment\Model\InfoInterface::class);

        $paymentDO->method('getPayment')->willReturn($paymentModel);
        $paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::CARD_NAME,         'John Doe'],
            [Config::CARD_NUMBER,       '4444333322221111'],
            [Config::CARD_EXPIRY_MONTH, '1'],
            [Config::CARD_EXPIRY_YEAR,  '2016'],
            [Config::CARD_CVN,          '123'],
        ]);

        $buildSubject = ['payment' => $paymentDO];
        $builder = new \Eway\DirectConnection\Gateway\Request\CardDetailsDataBuilder();
        $result = $builder->build($buildSubject);
        $this->assertEquals([
            Config::CUSTOMER => [
                Config::CARD_DETAILS => [
                    Config::CARD_NAME         => 'John Doe',
                    Config::CARD_NUMBER       => '4444333322221111',
                    Config::CARD_EXPIRY_MONTH => '01',
                    Config::CARD_EXPIRY_YEAR  => '2016',
                    Config::CARD_CVN          => '123',
                ]
            ]
        ], $result);
    }
}