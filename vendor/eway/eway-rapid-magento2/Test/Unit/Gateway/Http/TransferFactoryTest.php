<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Http;

use Eway\EwayRapid\Gateway\Http\TransferFactory;
use Eway\EwayRapid\Gateway\Request\BuilderComposite;
use Eway\EwayRapid\Model\Config\Source\Mode;
use Eway\Rapid\Enum\ApiMethod;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var TransferBuilder */
    protected $transferBuilder;

    /** @var  TransferFactory */
    protected $transferFactory;

    protected function setUp()
    {
        $this->config = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->transferBuilder = new TransferBuilder();
        $this->transferFactory = new TransferFactory($this->config, $this->transferBuilder);
    }

    public function testSandbox()
    {
        $this->config->method('getValue')->willReturnMap([
            ['mode', null, Mode::SANDBOX],
            ['sandbox_api_key', null, 'sandboxkey'],
            ['sandbox_api_password', null, 'sandboxpassword'],
            ['sandbox_endpoint', null, 'sandboxendpoint']
        ]);

        $request = [BuilderComposite::METHOD => ApiMethod::DIRECT];

        $transferObject = $this->transferFactory->create($request);
        $this->assertEquals('sandboxkey', $transferObject->getAuthUsername());
        $this->assertEquals('sandboxpassword', $transferObject->getAuthPassword());
        $this->assertEquals('sandboxendpoint', $transferObject->getUri());
        $this->assertEquals(ApiMethod::DIRECT, $transferObject->getMethod());
        $this->assertEquals([], $transferObject->getBody());
    }

    public function testLive()
    {
        $this->config->method('getValue')->willReturnMap([
            ['mode', null, Mode::LIVE],
            ['live_api_key', null, 'livekey'],
            ['live_api_password', null, 'livepassword'],
            ['live_endpoint', null, 'liveendpoint']
        ]);

        $request = [];

        $transferObject = $this->transferFactory->create($request);
        $this->assertEquals('livekey', $transferObject->getAuthUsername());
        $this->assertEquals('livepassword', $transferObject->getAuthPassword());
        $this->assertEquals('liveendpoint', $transferObject->getUri());
        $this->assertEquals([], $transferObject->getBody());
    }
}