<?php
namespace Eway\DirectConnection\Test\Unit\Gateway\Command;

use Eway\DirectConnection\Gateway\Command\AuthorizeStrategyCommand;
use Eway\EwayRapid\Model\Config;

class AuthorizeStrategyCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $paymentDO;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $paymentModel;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $config;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $tokenManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $commandPool;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $createTokenCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $chargeTokenMotoCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $updateTokenCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $chargeTokenCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $nonTokenAuthorizeCommand;

    /** @var  \Eway\DirectConnection\Gateway\Command\AuthorizeStrategyCommand::__construct */
    private $authorizeStrategyCommand;

    private $commandSubject;

    protected function setUp()
    {
        $this->config       = $this->getMockForAbstractClass(\Magento\Payment\Gateway\ConfigInterface::class);
        $this->tokenManager = $this->getMockForAbstractClass(\Eway\EwayRapid\Model\Customer\Token\ManagerInterface::class);
        $this->config->method('getValue')->willReturnMap([
            ['token_enabled', null, true]
        ]);

        $this->paymentDO    = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentModel = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentDO->method('getPayment')->willReturn($this->paymentModel);

        $this->commandPool              = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Command\CommandPoolInterface::class);
        $this->createTokenCommand       = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->chargeTokenCommand       = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->chargeTokenMotoCommand   = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->updateTokenCommand       = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->nonTokenAuthorizeCommand = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->commandPool->method('get')->willReturnMap([
            [AuthorizeStrategyCommand::CREATE_TOKEN, $this->createTokenCommand],
            [AuthorizeStrategyCommand::CHARGE_TOKEN_MOTO, $this->chargeTokenMotoCommand],
            [AuthorizeStrategyCommand::UPDATE_TOKEN, $this->updateTokenCommand],
            [AuthorizeStrategyCommand::CHARGE_TOKEN, $this->chargeTokenCommand],
            [AuthorizeStrategyCommand::NON_TOKEN_AUTHORIZE, $this->nonTokenAuthorizeCommand],
        ]);

        $this->authorizeStrategyCommand = new AuthorizeStrategyCommand($this->commandPool, $this->config, $this->tokenManager);
        $this->commandSubject = ['payment' => $this->paymentDO];
    }

    public function testNonTokenAuthorize()
    {
        $this->commandPool->expects($this->once())->method('get');
        $this->nonTokenAuthorizeCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

    public function testTokenActionNewNormal()
    {
        $this->paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ACTION, Config::TOKEN_ACTION_NEW]
        ]);
        $this->commandPool->expects($this->exactly(2))->method('get');
        $this->createTokenCommand->expects($this->once())->method('execute');
        $this->chargeTokenMotoCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

    public function testTokenActionNewException()
    {
        $this->paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ACTION, Config::TOKEN_ACTION_NEW],
            [Config::TOKEN_ID, 1]
        ]);

        $this->commandPool->expects($this->exactly(2))->method('get');
        $this->createTokenCommand->expects($this->once())->method('execute');
        $this->chargeTokenMotoCommand->expects($this->once())->method('execute')->willThrowException(new \Exception());
        $this->tokenManager->expects($this->once())->method('deleteToken')->with(1);
        $this->setExpectedException(\Exception::class);

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

    public function testTokenActionUpdate()
    {
        $this->paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ACTION, Config::TOKEN_ACTION_UPDATE]
        ]);
        $this->commandPool->expects($this->exactly(2))->method('get');
        $this->updateTokenCommand->expects($this->once())->method('execute');
        $this->chargeTokenMotoCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

    public function testTokenActionUse()
    {
        $this->paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ACTION, Config::TOKEN_ACTION_USE]
        ]);
        $this->commandPool->expects($this->exactly(1))->method('get');
        $this->chargeTokenCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

}