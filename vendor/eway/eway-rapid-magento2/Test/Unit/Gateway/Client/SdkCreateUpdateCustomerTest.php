<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Client;

use Eway\EwayRapid\Gateway\Client\Sdk;
use Eway\EwayRapid\Model\Config;
use Eway\Rapid\Enum\ApiMethod;
use Eway\Rapid\Model\Response\CreateCustomerResponse;
use Psr\Log\LoggerInterface;

class SdkCreateUpdateCustomerTest extends \PHPUnit_Framework_TestCase
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

    public function testCreateCustomer()
    {
        $transferObject = $this->createTransferObject();

        $response = new CreateCustomerResponse();
        $response->setAttribute(Config::TRANSACTION_STATUS, 1);

        $this->client
            ->expects($this->once())
            ->method('createCustomer')->with(
                $this->equalTo(ApiMethod::DIRECT),
                $this->equalTo([]))
            ->willReturn($response);

        $sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::CREATE_CUSTOMER);
        $result = $sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }

    public function testUpdateCustomer()
    {
        $transferObject = $this->createTransferObject();

        $response = new CreateCustomerResponse();
        $response->setAttribute(Config::TRANSACTION_STATUS, 1);

        $this->client
            ->expects($this->once())
            ->method('updateCustomer')->with(
                $this->equalTo(ApiMethod::DIRECT),
                $this->equalTo([]))
            ->willReturn($response);

        $sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::UPDATE_CUSTOMER);
        $result = $sdk->placeRequest($transferObject);
        $this->assertEquals(1, $result[Config::TRANSACTION_STATUS]);
    }

    private function createTransferObject()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getMethod')->willReturn(ApiMethod::DIRECT);
        $transferObject->method('getBody')->willReturn([]);
        return $transferObject;
    }
}