<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Model\Config;

class PaymentDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $orderMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $paymentDO;
    private $config;
    /**
     * @var \Eway\EwayRapid\Gateway\Request\PaymentDataBuilder
     */
    private $paymentDataBuilder;

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(\Eway\EwayRapid\Gateway\QuoteAdapter::class)->disableOriginalConstructor()->getMock();
        $this->orderMock->method('getCurrencyCode')->willReturn('AUD');
        $this->orderMock->method('getOrderIncrementId')->willReturn('12345');
        $this->paymentDO = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentDO->method('getOrder')->willReturn($this->orderMock);
        $this->config = $this->getMockForAbstractClass(\Magento\Payment\Gateway\ConfigInterface::class);
        $this->paymentDataBuilder = new \Eway\EwayRapid\Gateway\Request\PaymentDataBuilder($this->config);
    }

    public function testBuildSimplePayment()
    {
        $this->config->method('getValue')->willReturnMap([
            ['invoice_reference', null, false],
            ['invoice_description', null, false],
        ]);

        $buildSubject = [
            'amount' => 123.1234,
            'payment' => $this->paymentDO,
        ];

        $result = $this->paymentDataBuilder->build($buildSubject);

        $this->assertEquals([
            Config::PAYMENT => [
                Config::TOTAL_AMOUNT  => 12312,
                Config::CURRENCY_CODE => 'AUD'
            ]
        ], $result);
    }

    public function testBuildComplexPayment()
    {
        $this->config->method('getValue')->willReturnMap([
            ['invoice_reference', null, true],
            ['invoice_description', null, false],
        ]);

        $buildSubject = [
            'amount' => 123.1234,
            'payment' => $this->paymentDO,
        ];

        $result = $this->paymentDataBuilder->build($buildSubject);

        $this->assertEquals([
            Config::PAYMENT => [
                Config::TOTAL_AMOUNT  => 12312,
                Config::CURRENCY_CODE => 'AUD',
                Config::INVOICE_REFERENCE => '12345',
                Config::INVOICE_NUMBER => '12345'
            ]
        ], $result);
    }
}