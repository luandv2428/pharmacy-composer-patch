<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Client;

use Eway\EwayRapid\Gateway\Client\Sdk;
use Eway\EwayRapid\Model\Config;
use Eway\Rapid\Enum\ApiMethod;
use Eway\Rapid\Model\Response\CreateTransactionResponse;
use Psr\Log\LoggerInterface;

class SdkCreateTransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sdk
     */
    private $sdk;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $client;

    private $logger;

    private $config;

    private $clientFactory;

    protected function setUp()
    {
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->client = $this->getMockBuilder(\Eway\Rapid\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientFactory = $this->getMockBuilder(\Eway\EwayRapid\Gateway\Client\ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory->method('create')->willReturn($this->client);

        $this->config = $this->getMockForAbstractClass(\Magento\Payment\Gateway\ConfigInterface::class);
        $this->config->method('getValue')->willReturnMap([['debug', null, '1']]);
        $this->logger->expects($this->atLeastOnce())->method('debug');

        $this->sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::CREATE_TRANSACTION);
    }

    public function testPlaceRequestSuccess()
    {
        $transferObject = $this->createTransferObject();

        $response = new CreateTransactionResponse();
        $response->setAttribute(Config::TRANSACTION_STATUS, 1);

        $this->client
            ->expects($this->once())
            ->method('createTransaction')->with(
                $this->equalTo(ApiMethod::DIRECT),
                $this->equalTo([]))
            ->willReturn($response);

        $result = $this->sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }

    public function testPlaceRequestError()
    {
        $transferObject = $this->createTransferObject();

        $response = new CreateTransactionResponse();
        $response->addError(\Eway\Rapid\Contract\Client::ERROR_HTTP_AUTHENTICATION_ERROR);

        $this->client
            ->expects($this->once())
            ->method('createTransaction')->with(
                $this->equalTo(ApiMethod::DIRECT),
                $this->equalTo([]))
            ->willReturn($response);

        $this->setExpectedException(\Magento\Framework\Exception\PaymentException::class);
        $this->sdk->placeRequest($transferObject);
    }

    public function testOperationNotExisted()
    {
        $transferObject = $this->createTransferObject();

        $this->client
            ->expects($this->never())
            ->method('createTransaction');

        $this->setExpectedException(\InvalidArgumentException::class);
        $sdk = new Sdk($this->logger, $this->config, $this->clientFactory, 'not_existed_operation');
        $sdk->placeRequest($transferObject);
    }

    private function createTransferObject()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getMethod')->willReturn(ApiMethod::DIRECT);
        $transferObject->method('getBody')->willReturn([]);
        return $transferObject;
    }
}