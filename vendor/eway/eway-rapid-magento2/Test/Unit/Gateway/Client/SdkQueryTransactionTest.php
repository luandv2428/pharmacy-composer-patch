<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Client;

use Eway\EwayRapid\Gateway\Client\Sdk;
use Eway\EwayRapid\Model\Config;
use Eway\Rapid\Model\Response\QueryTransactionResponse;
use Eway\Rapid\Model\Transaction;
use Psr\Log\LoggerInterface;

class SdkQueryTransactionTest extends \PHPUnit_Framework_TestCase
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
        $this->config->method('getValue')->willReturnMap([['debug', null, 0]]);
        $this->logger->expects($this->never())->method('debug');

        $this->sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::QUERY_TRANSACTION);
    }

    public function testPlaceRequestSuccess()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::ACCESS_CODE => 'abc123']);

        $response = new QueryTransactionResponse();
        $transaction = new Transaction();
        $transaction->setAttribute(Config::TRANSACTION_STATUS, 1);

        $response->setAttribute('Transactions', [$transaction]);

        $this->client
            ->expects($this->once())
            ->method('queryTransaction')->with(
                $this->equalTo('abc123'))
            ->willReturn($response);

        $result = $this->sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }

    public function testPlaceRequestError()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::ACCESS_CODE => 'abc123']);

        $response = new QueryTransactionResponse();
        $response->setAttribute('Transactions', []);

        $this->client
            ->expects($this->once())
            ->method('queryTransaction')->with(
                $this->equalTo('abc123'))
            ->willReturn($response);

        $this->setExpectedException(\Magento\Framework\Exception\PaymentException::class);
        $this->sdk->placeRequest($transferObject);
    }
}