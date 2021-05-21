<?php
namespace Eway\IFrame\Test\Unit\Gateway\Command;

use Eway\IFrame\Gateway\Command\AuthorizeStrategyCommand;
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
    private $createTokenAuthorizeCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $queryAndCreateTokenCommand;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $nonTokenAuthorizeCommand;

    /** @var  \Eway\DirectConnection\Gateway\Command\AuthorizeStrategyCommand::__construct */
    private $authorizeStrategyCommand;

    private $commandSubject;

    protected function setUp()
    {
        $this->config       = $this->getMockForAbstractClass(\Magento\Payment\Gateway\ConfigInterface::class);
        $this->config->method('getValue')->willReturnMap([
            ['token_enabled', null, true]
        ]);

        $this->paymentDO    = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentModel = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentDO->method('getPayment')->willReturn($this->paymentModel);

        $this->commandPool                 = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Command\CommandPoolInterface::class);
        $this->createTokenAuthorizeCommand = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->queryAndCreateTokenCommand  = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->nonTokenAuthorizeCommand    = $this->getMockForAbstractClass(\Magento\Payment\Gateway\CommandInterface::class);
        $this->commandPool->method('get')->willReturnMap([
            [AuthorizeStrategyCommand::CREATE_TOKEN_AUTHORIZE, $this->createTokenAuthorizeCommand],
            [AuthorizeStrategyCommand::QUERY_AND_CREATE_TOKEN, $this->queryAndCreateTokenCommand],
            [AuthorizeStrategyCommand::NON_TOKEN_AUTHORIZE, $this->nonTokenAuthorizeCommand],
        ]);

        $this->authorizeStrategyCommand = new AuthorizeStrategyCommand($this->commandPool, $this->config);
        $this->commandSubject = ['payment' => $this->paymentDO];
    }

    public function testNonTokenAuthorize()
    {
        $this->commandPool->expects($this->once())->method('get');
        $this->nonTokenAuthorizeCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }

    public function testTokenActionNew()
    {
        $this->paymentModel->method('getAdditionalInformation')->willReturnMap([
            [Config::TOKEN_ACTION, Config::TOKEN_ACTION_NEW]
        ]);
        $this->commandPool->expects($this->exactly(2))->method('get');
        $this->createTokenAuthorizeCommand->expects($this->once())->method('execute');
        $this->queryAndCreateTokenCommand->expects($this->once())->method('execute');

        $this->authorizeStrategyCommand->execute($this->commandSubject);
    }
}