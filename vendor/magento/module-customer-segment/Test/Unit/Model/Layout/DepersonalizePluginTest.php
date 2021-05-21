<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerSegment\Test\Unit\Model\Layout;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session;
use Magento\CustomerSegment\Helper\Data;
use Magento\CustomerSegment\Model\Layout\DepersonalizePlugin;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Tests Magento\CustomerSegment\Model\Layout\DepersonalizePlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var LayoutInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var HttpContext|PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * @var Manager|PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * @var Config|PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheConfig;

    /**
     * @var StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->customerSessionMock = $this->createPartialMock(
            Session::class,
            ['getCustomerSegmentIds', 'setCustomerSegmentIds']
        );
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->cacheConfig = $this->createMock(Config::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'customerSession' => $this->customerSessionMock,
                'request' => $this->requestMock,
                'moduleManager' => $this->moduleManagerMock,
                'httpContext' => $this->httpContextMock,
                'cacheConfig' => $this->cacheConfig,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test afterGenerateElements method when Magento_PageCache is enabled and the layout is cachable
     *
     * @param bool $isCustomerLoggedIn
     * @return void
     * @dataProvider dataProviderBeforeGenerateXml
     */
    public function testAfterGenerateElements(bool $isCustomerLoggedIn): void
    {
        $websiteId = 1;
        $customerSegmentIds = [1 => [1, 2, 3]];
        $expectedCustomerSegmentIds = [1, 2, 3];
        $defaultCustomerSegmentIds = [];

        if (!$isCustomerLoggedIn) {
            $defaultCustomerSegmentIds = $expectedCustomerSegmentIds;
        }

        $this->moduleManagerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfig->expects($this->exactly(2))
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->exactly(2))
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->exactly(2))
            ->method('isCacheable')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerSegmentIds')
            ->will($this->returnValue($customerSegmentIds));
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerSegmentIds')
            ->with($this->equalTo($customerSegmentIds));
        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with(null)
            ->willReturn($websiteMock);

        $this->httpContextMock->expects($this->once())
            ->method('getValue')
            ->with(Context::CONTEXT_AUTH)
            ->willReturn($isCustomerLoggedIn);
        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                $this->equalTo(Data::CONTEXT_SEGMENT),
                $this->equalTo($expectedCustomerSegmentIds),
                $this->equalTo($defaultCustomerSegmentIds)
            );

        $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * @return array
     */
    public function dataProviderBeforeGenerateXml(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testBeforeGenerateXmlWithNoWebsite()
    {
        $websiteId = 2;
        $customerSegmentIds = [1 => [1, 2, 3]];
        $expectedCustomerSegmentIds = [];
        $defaultCustomerSegmentIds = [];
        $this->moduleManagerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfig->expects($this->exactly(2))
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->exactly(2))
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->exactly(2))
            ->method('isCacheable')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerSegmentIds')
            ->will($this->returnValue($customerSegmentIds));
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerSegmentIds')
            ->with($this->equalTo($customerSegmentIds));
        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with(null)
            ->willReturn($websiteMock);
        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                $this->equalTo(Data::CONTEXT_SEGMENT),
                $this->equalTo($expectedCustomerSegmentIds),
                $this->equalTo($defaultCustomerSegmentIds)
            );
        $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * testUsualBehaviorIsAjax
     */
    public function testUsualBehaviorIsAjax()
    {
        $this->moduleManagerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfig->expects($this->exactly(2))
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->exactly(2))
            ->method('isAjax')
            ->will($this->returnValue(true));
        $this->layoutMock->expects($this->never())
            ->method('isCacheable');
        $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * testUsualBehaviorNonCacheable
     */
    public function testUsualBehaviorNonCacheable()
    {
        $this->moduleManagerMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfig->expects($this->exactly(2))
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->exactly(2))
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->exactly(2))
            ->method('isCacheable')
            ->will($this->returnValue(false));
        $this->customerSessionMock->expects($this->never())
            ->method('setCustomerSegmentIds');
        $this->plugin->beforeGenerateXml($this->layoutMock);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
