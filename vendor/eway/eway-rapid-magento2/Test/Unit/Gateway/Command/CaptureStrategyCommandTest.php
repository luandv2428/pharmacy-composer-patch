<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Command;

use Eway\EwayRapid\Gateway\Command\CaptureStrategyCommand;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class CaptureStrategyCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $commandPool;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $preAuthCaptureCommand;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $saleCommand;

    /** @var  CaptureStrategyCommand */
    private $captureStrategyCommand;

    protected function setUp()
    {
        $this->commandPool = $this->getMockForAbstractClass(CommandPoolInterface::class);

        $this->preAuthCaptureCommand = $this->getMockForAbstractClass(CommandInterface::class);
        $this->saleCommand = $this->getMockForAbstractClass(CommandInterface::class);

        $this->commandPool
            ->method('get')
            ->willReturnMap([
                [CaptureStrategyCommand::PRE_AUTH_CAPTURE, $this->preAuthCaptureCommand],
                [CaptureStrategyCommand::SALE, $this->saleCommand]
            ]);

        $registry = new \Magento\Framework\Registry();
        $registry->register('ewayrapid_should_verify_fraud', false);
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $orderService = $this->getMockBuilder(\Eway\EwayRapid\Model\OrderService::class)
            ->disableOriginalConstructor()->getMock();
        $this->captureStrategyCommand = new CaptureStrategyCommand($this->commandPool, $registry, $objectManager, $orderService);
    }

    public function testExecutePreAuthCapture()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->once())->method('getAuthorizationTransaction')->willReturn(true);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);
        $commandSubject = ['payment' => $paymentDO];

        $this->commandPool->expects($this->once())
            ->method('get')
            ->with($this->equalTo(CaptureStrategyCommand::PRE_AUTH_CAPTURE));

        $this->preAuthCaptureCommand->expects($this->once())->method('execute');
        $this->saleCommand->expects($this->never())->method('execute');

        $this->captureStrategyCommand->execute($commandSubject);
    }

    public function testExecuteSale()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()->getMock();
        $paymentInfo->expects($this->once())->method('getAuthorizationTransaction')->willReturn(false);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);
        $commandSubject = ['payment' => $paymentDO];

        $this->commandPool->expects($this->once())
            ->method('get')
            ->with($this->equalTo(CaptureStrategyCommand::SALE));

        $this->preAuthCaptureCommand->expects($this->never())->method('execute');
        $this->saleCommand->expects($this->once())->method('execute');

        $this->captureStrategyCommand->execute($commandSubject);
    }

    public function testReadPaymentError()
    {
        $commandSubject = [];

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->captureStrategyCommand->execute($commandSubject);
    }

    public function testAssertOrderPaymentError()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockForAbstractClass(\Magento\Payment\Model\InfoInterface::class);
        $paymentDO->expects($this->once())->method('getPayment')->willReturn($paymentInfo);
        $commandSubject = ['payment' => $paymentDO];

        $this->setExpectedException(\LogicException::class);
        $this->captureStrategyCommand->execute($commandSubject);
    }
}