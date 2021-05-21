<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftWrapping\Test\Unit\Model\Plugin;

use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\GiftWrapping\Model\Plugin\OrderGiftWrapping;

/**
 * Class OrderGiftWrappingTest
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class OrderGiftWrappingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderGiftWrapping
     */
    private $plugin;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var OrderExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var OrderExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionFactoryMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->setMethods([
                'getExtensionAttributes',
                'setExtensionAttributes',
                'getGwId',
                'getGwAllowGiftReceipt',
                'getGwAddCard',
                'getGwBasePrice',
                'getGwPrice',
                'getGwItemsBasePrice',
                'getGwItemsPrice',
                'getGwCardBasePrice',
                'getGwCardPrice',
                'getGwBaseTaxAmount',
                'getGwTaxAmount',
                'getGwItemsBaseTaxAmount',
                'getGwItemsTaxAmount',
                'getGwCardBaseTaxAmount',
                'getGwCardTaxAmount',
                'getGwBasePriceInclTax',
                'getGwPriceInclTax',
                'getGwItemsBasePriceInclTax',
                'getGwItemsPriceInclTax',
                'getGwCardBasePriceInclTax',
                'getGwCardPriceInclTax',
                'getGwBasePriceInvoiced',
                'getGwPriceInvoiced',
                'getGwItemsBasePriceInvoiced',
                'getGwItemsPriceInvoiced',
                'getGwCardBasePriceInvoiced',
                'getGwCardPriceInvoiced',
                'getGwBaseTaxAmountInvoiced',
                'getGwTaxAmountInvoiced',
                'getGwItemsBaseTaxInvoiced',
                'getGwItemsTaxInvoiced',
                'getGwCardBaseTaxInvoiced',
                'getGwCardTaxInvoiced',
                'getGwBasePriceRefunded',
                'getGwPriceRefunded',
                'getGwItemsBasePriceRefunded',
                'getGwItemsPriceRefunded',
                'getGwCardBasePriceRefunded',
                'getGwCardPriceRefunded',
                'getGwBaseTaxAmountRefunded',
                'getGwTaxAmountRefunded',
                'getGwItemsBaseTaxRefunded',
                'getGwItemsTaxRefunded',
                'getGwCardBaseTaxRefunded',
                'getGwCardTaxRefunded'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->extensionAttributeMock = $this->getMockBuilder(OrderExtension::class)
            ->setMethods([
                'setGwId',
                'setGwAllowGiftReceipt',
                'setGwAddCard',
                'setGwBasePrice',
                'setGwPrice',
                'setGwItemsBasePrice',
                'setGwItemsPrice',
                'setGwCardBasePrice',
                'setGwCardPrice',
                'setGwBaseTaxAmount',
                'setGwTaxAmount',
                'setGwItemsBaseTaxAmount',
                'setGwItemsTaxAmount',
                'setGwCardBaseTaxAmount',
                'setGwCardTaxAmount',
                'setGwBasePriceInclTax',
                'setGwPriceInclTax',
                'setGwItemsBasePriceInclTax',
                'setGwItemsPriceInclTax',
                'setGwCardBasePriceInclTax',
                'setGwCardPriceInclTax',
                'setGwBasePriceInvoiced',
                'setGwPriceInvoiced',
                'setGwItemsBasePriceInvoiced',
                'setGwItemsPriceInvoiced',
                'setGwCardBasePriceInvoiced',
                'setGwCardPriceInvoiced',
                'setGwBaseTaxAmountInvoiced',
                'setGwTaxAmountInvoiced',
                'setGwItemsBaseTaxInvoiced',
                'setGwItemsTaxInvoiced',
                'setGwCardBaseTaxInvoiced',
                'setGwCardTaxInvoiced',
                'setGwBasePriceRefunded',
                'setGwPriceRefunded',
                'setGwItemsBasePriceRefunded',
                'setGwItemsPriceRefunded',
                'setGwCardBasePriceRefunded',
                'setGwCardPriceRefunded',
                'setGwBaseTaxAmountRefunded',
                'setGwTaxAmountRefunded',
                'setGwItemsBaseTaxRefunded',
                'setGwItemsTaxRefunded',
                'setGwCardBaseTaxRefunded',
                'setGwCardTaxRefunded'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderExtensionFactoryMock = $this->getMockBuilder(OrderExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new OrderGiftWrapping(
            $this->orderExtensionFactoryMock
        );
    }

    public function testAfterGet()
    {
        $returnValue = 10;

        $this->orderMock->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributeMock);

        $this->orderMock->expects(static::once())
            ->method('getGwId')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwId')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwAllowGiftReceipt')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwAllowGiftReceipt')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwAddCard')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwAddCard')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBasePrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBasePrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwPrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwPrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBasePrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBasePrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsPrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsPrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBasePrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBasePrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardPrice')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardPrice')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBaseTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBaseTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBaseTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBaseTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBaseTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBaseTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardTaxAmount')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardTaxAmount')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBasePriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBasePriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwPriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwPriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBasePriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBasePriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsPriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsPriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBasePriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBasePriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardPriceInclTax')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardPriceInclTax')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBasePriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBasePriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwPriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwPriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBasePriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBasePriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsPriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsPriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBasePriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBasePriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardPriceInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardPriceInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBaseTaxAmountInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBaseTaxAmountInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwTaxAmountInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwTaxAmountInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBaseTaxInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBaseTaxInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsTaxInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsTaxInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBaseTaxInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBaseTaxInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardTaxInvoiced')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardTaxInvoiced')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBasePriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBasePriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwPriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwPriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBasePriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBasePriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsPriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsPriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBasePriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBasePriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardPriceRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardPriceRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwBaseTaxAmountRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwBaseTaxAmountRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwTaxAmountRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwTaxAmountRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsBaseTaxRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsBaseTaxRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwItemsTaxRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwItemsTaxRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardBaseTaxRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardBaseTaxRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        $this->orderMock->expects(static::once())
            ->method('getGwCardTaxRefunded')
            ->willReturn($returnValue);
        $this->extensionAttributeMock->expects(static::once())
            ->method('setGwCardTaxRefunded')
            ->with($returnValue)
            ->willReturnSelf();
        
        $this->orderMock->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributeMock)
            ->willReturnSelf();

        $this->plugin->afterGet($this->subjectMock, $this->orderMock);
    }
}
