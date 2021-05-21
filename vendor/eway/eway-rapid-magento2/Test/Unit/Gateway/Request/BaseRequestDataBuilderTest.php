<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Gateway\Request\BaseRequestDataBuilder;
use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Config\Source\PaymentAction;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class BaseRequestDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $config;

    private $versionMock;

    protected function setUp()
    {
        $this->config = $this->getMockForAbstractClass(ConfigInterface::class);
        $versionMock = $this->getMockBuilder(\Eway\EwayRapid\Model\Version::class)
            ->disableOriginalConstructor()->getMock();
        $versionMock->method('getMagentoVersion')->willReturn('Magento 2');
        $versionMock->method('getEwayVersion')->willReturn('EwayRapid');
        $this->versionMock = $versionMock;
    }

    public function testTransactionPurchaseCaptureSaveToken()
    {
        $this->config->method('getValue')->willReturnMap([
            ['payment_action', 1, PaymentAction::ACTION_AUTHORIZE_CAPTURE],
            ['token_enabled', 1, true]
        ]);

        $builder = new BaseRequestDataBuilder($this->config, $this->versionMock);

        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $paymentInfo->method('getAdditionalInformation')->willReturnMap([[Config::TOKEN_ACTION, Config::TOKEN_ACTION_NEW]]);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getRemoteIp')->willReturn('127.0.0.1');
        $paymentDO->expects($this->once())->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDO];

        $result = $builder->build($buildSubject);

        $this->assertEquals(
            [
                Config::CUSTOMER_IP      => '127.0.0.1',
                Config::TRANSACTION_TYPE => Config::PURCHASE,
                Config::CAPTURE          => true,
                Config::SAVE_CUSTOMER    => true,
                Config::CUSTOMER_READ_ONLY => true,
                Config::DEVICE_ID       => 'Magento 2 - eWAY EwayRapid'
            ],
            $result
        );
    }

    public function testTransactionMOTOPreAuthNoSaveToken()
    {
        $this->config->method('getValue')->willReturnMap([
            ['payment_action', 1, PaymentAction::ACTION_AUTHORIZE],
            ['token_enabled', 1, true]
        ]);
        $builder = new BaseRequestDataBuilder($this->config, $this->versionMock, Config::MOTO);

        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $paymentInfo->method('getAdditionalInformation')->willReturnMap([[Config::TOKEN_ACTION, Config::TOKEN_ACTION_USE]]);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getRemoteIp')->willReturn('127.0.0.1');
        $paymentDO->expects($this->once())->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDO];

        $result = $builder->build($buildSubject);

        $this->assertEquals(
            [
                Config::CUSTOMER_IP      => '127.0.0.1',
                Config::TRANSACTION_TYPE => Config::MOTO,
                Config::CAPTURE          => false,
                Config::CUSTOMER_READ_ONLY => true,
                Config::DEVICE_ID       => 'Magento 2 - eWAY EwayRapid'
            ],
            $result
        );
    }

    public function testTransactionMOTOPreAuthDisableToken()
    {
        $this->config->method('getValue')->willReturnMap([
            ['payment_action', 1, PaymentAction::ACTION_AUTHORIZE],
            ['token_enabled', 1, false]
        ]);
        $builder = new BaseRequestDataBuilder($this->config, $this->versionMock, Config::MOTO);

        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentDO->expects($this->never())->method('getPayment');
        $order = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getRemoteIp')->willReturn('127.0.0.1');
        $paymentDO->expects($this->once())->method('getOrder')->willReturn($order);
        $buildSubject = ['payment' => $paymentDO];

        $result = $builder->build($buildSubject);

        $this->assertEquals(
            [
                Config::CUSTOMER_IP      => '127.0.0.1',
                Config::TRANSACTION_TYPE => Config::MOTO,
                Config::CAPTURE          => false,
                Config::CUSTOMER_READ_ONLY => true,
                Config::DEVICE_ID       => 'Magento 2 - eWAY EwayRapid'
            ],
            $result
        );
    }
}