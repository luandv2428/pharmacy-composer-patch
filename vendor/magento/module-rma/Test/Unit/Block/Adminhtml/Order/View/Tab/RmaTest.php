<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rma\Test\Unit\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Order RMA tab test
 */
class RmaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Rma\Block\Adminhtml\Order\View\Tab\Rma
     */
    protected $rmaTab;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderItemMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getQtyReturned', 'getQtyShipped']
        );
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with($this->equalTo('current_order'))
            ->will($this->returnValue($this->orderMock));
        $this->orderMock->expects($this->any())
            ->method('getItemsCollection')
            ->will($this->returnValue([$this->orderItemMock]));

        $this->rmaTab = $this->objectManager->getObject(
            \Magento\Rma\Block\Adminhtml\Order\View\Tab\Rma::class,
            [
                'coreRegistry' => $this->registryMock
            ]
        );
    }

    /**
     */
    public function testCanShowTabWhenProductShipped()
    {
        $expectedResult = true;
        $this->orderItemMock->expects($this->any())
            ->method('getQtyShipped')
            ->will($this->returnValue(1));
        $this->orderItemMock->expects($this->any())
            ->method('getQtyReturned')
            ->will($this->returnValue(0));
        $this->assertEquals($expectedResult, $this->rmaTab->canShowTab());
    }

    /**
     */
    public function testCanShowTabWhenProductReturned()
    {
        $expectedResult = true;
        $this->orderItemMock->expects($this->any())
            ->method('getQtyShipped')
            ->will($this->returnValue(0));
        $this->orderItemMock->expects($this->any())
            ->method('getQtyReturned')
            ->will($this->returnValue(1));
        $this->assertEquals($expectedResult, $this->rmaTab->canShowTab());
    }

    /**
     */
    public function testCanNotShowTabWhenProductNotShipped()
    {
        $expectedResult = false;
        $this->orderItemMock->expects($this->any())
            ->method('getQtyShipped')
            ->will($this->returnValue(0));
        $this->orderItemMock->expects($this->any())
            ->method('getQtyReturned')
            ->will($this->returnValue(0));
        $this->assertEquals($expectedResult, $this->rmaTab->canShowTab());
    }
}
