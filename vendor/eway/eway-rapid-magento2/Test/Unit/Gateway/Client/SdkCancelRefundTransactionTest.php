<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Client;

use Eway\EwayRapid\Gateway\Client\Sdk;
use Eway\EwayRapid\Model\Config;
use Eway\Rapid\Enum\ApiMethod;
use Eway\Rapid\Model\Response\CreateTransactionResponse;
use Eway\Rapid\Model\Response\RefundResponse;
use Psr\Log\LoggerInterface;

class SdkCancelRefundTransactionTest extends \PHPUnit_Framework_TestCase
{
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
    }

    public function testCancelTransaction()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::TRANSACTION_ID => 123]);

        $response = new RefundResponse();
        $response->setAttribute(Config::TRANSACTION_STATUS, 1);

        $this->client
            ->expects($this->once())
            ->method('cancelTransaction')->with(
                $this->equalTo(123))
            ->willReturn($response);

        $sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::CANCEL_TRANSACTION);
        $result = $sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }

    public function testRefundTransaction()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::TRANSACTION_ID => 123]);

        $response = new RefundResponse();
        $response->setAttribute(Config::TRANSACTION_STATUS, 1);

        $this->client
            ->expects($this->once())
            ->method('refund')->with(
                $this->equalTo([Config::TRANSACTION_ID => 123]))
            ->willReturn($response);

        $sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::REFUND_TRANSACTION);
        $result = $sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }
}