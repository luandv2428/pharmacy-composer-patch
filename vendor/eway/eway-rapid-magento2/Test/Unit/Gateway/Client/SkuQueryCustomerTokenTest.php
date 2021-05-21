<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Client;

use Eway\EwayRapid\Gateway\Client\Sdk;
use Eway\EwayRapid\Model\Config;
use Eway\Rapid\Model\Customer;
use Eway\Rapid\Model\Response\QueryCustomerResponse;
use Psr\Log\LoggerInterface;

class SkuQueryCustomerTokenTest extends \PHPUnit_Framework_TestCase
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

        $this->sdk = new Sdk($this->logger, $this->config, $this->clientFactory, Sdk::QUERY_CUSTOMER_TOKEN);
    }

    public function testPlaceRequestSuccess()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::TOKEN_CUSTOMER_ID => 'token123']);

        $response = new QueryCustomerResponse();
        $customer = new Customer();
        $customer->setAttribute(Config::FIRST_NAME, 'John Doe');

        $response->setAttribute('Customers', [$customer]);

        $this->client
            ->expects($this->once())
            ->method('queryCustomer')->with(
                $this->equalTo('token123'))
            ->willReturn($response);

        $result = $this->sdk->placeRequest($transferObject);
        $this->assertEquals('John Doe', $result[Config::CUSTOMER][Config::FIRST_NAME]);
    }

    public function testPlaceRequestError()
    {
        $transferObject = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Http\TransferInterface::class);
        $transferObject->method('getBody')->willReturn([Config::TOKEN_CUSTOMER_ID => 'token123']);

        $response = new QueryCustomerResponse();
        $response->setAttribute('Customers', []);

        $this->client
            ->expects($this->once())
            ->method('queryCustomer')->with(
                $this->equalTo('token123'))
            ->willReturn($response);

        $this->setExpectedException(\Magento\Framework\Exception\PaymentException::class);
        $this->sdk->placeRequest($transferObject);
    }
}