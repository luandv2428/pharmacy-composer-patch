<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftWrapping\Test\Unit\Observer;

/**
 * Test cases for adding gift wrapping items into payment checkout
 */
class AddPaymentGiftWrappingItemTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\GiftWrapping\Observer\AddPaymentGiftWrappingItem */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\GiftWrapping\Observer\AddPaymentGiftWrappingItem::class
        );
        $this->_event = new \Magento\Framework\DataObject();
        $this->_observer = new \Magento\Framework\Event\Observer(['event' => $this->_event]);
    }

    /**
     * @param float $amount
     * @dataProvider addPaymentGiftWrappingItemTotalCardDataProvider
     */
    public function testAddPaymentGiftWrappingItemTotalCard($amount)
    {
        $salesModel = $this->getMockForAbstractClass(\Magento\Payment\Model\Cart\SalesModel\SalesModelInterface::class);
        $salesModel->expects($this->once())->method('getAllItems')->will($this->returnValue([]));
        $salesModel->expects($this->any())->method('getDataUsingMethod')->will(
            $this->returnCallback(
                function ($key) use ($amount) {
                    if ($key == 'gw_card_base_price') {
                        return $amount;
                    } elseif ($key == 'gw_add_card' && is_float($amount)) {
                        return true;
                    } else {
                        return null;
                    }
                }
            )
        );
        $cart = $this->createMock(\Magento\Payment\Model\Cart::class);
        $cart->expects($this->any())->method('getSalesModel')->will($this->returnValue($salesModel));
        if ($amount) {
            $cart->expects($this->once())->method('addCustomItem')->with(__('Printed Card'), 1, $amount);
        } else {
            $cart->expects($this->never())->method('addCustomItem');
        }
        $this->_event->setCart($cart);
        $this->_model->execute($this->_observer);
    }

    public function addPaymentGiftWrappingItemTotalCardDataProvider()
    {
        return [[null], [0], [0.12]];
    }

    /**
     * Tests all possible variations of carts with and without gift wrapping
     *
     * @param array $items
     * @param array $salesModelData
     * @param float $expected
     * @dataProvider addPaymentGiftWrappingItemTotalWrappingDataProvider
     */
    public function testAddPaymentGiftWrappingItemTotalWrapping(array $items, array $salesModelData, float $expected)
    {
        $salesModel = $this->getMockForAbstractClass(\Magento\Payment\Model\Cart\SalesModel\SalesModelInterface::class);
        $salesModelData = new \Magento\Framework\DataObject($salesModelData);
        $salesModel->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($items));
        $salesModel->expects($this->any())
            ->method('getDataUsingMethod')
            ->will($this->returnCallback([$salesModelData, 'getDataUsingMethod']));

        $cart = $this->createMock(\Magento\Payment\Model\Cart::class);
        $cart->expects($this->any())->method('getSalesModel')->will($this->returnValue($salesModel));
        if ($expected) {
            $cart->expects($this->once())->method('addCustomItem')->with(__('Gift Wrapping'), 1, $expected);
        } else {
            $cart->expects($this->never())->method('addCustomItem');
        }
        $this->_event->setCart($cart);
        $this->_model->execute($this->_observer);
    }

    /**
     * @return array
     */
    public function addPaymentGiftWrappingItemTotalWrappingDataProvider()
    {
        $data = [];

        $qtyAttributeVariations = [
            // use case: quote
            \Magento\Quote\Model\Quote\Item::class => 'qty',
            // use case: order
            \Magento\Sales\Model\Order\Item::class => 'qty_ordered',
        ];

        foreach ($qtyAttributeVariations as $contract => $qtyAttribute) {
            $originalItems = [
                ['gw_id' => 1, 'gw_base_price' => 0.3, $qtyAttribute => 1, 'parent_item' => true],
                ['gw_id' => null, 'gw_base_price' => 0.3, $qtyAttribute => 1],
                ['gw_id' => 1, 'gw_base_price' => 0.0, $qtyAttribute => 1],
                ['gw_id' => 2, 'gw_base_price' => null, $qtyAttribute => 1],
                ['gw_id' => 3, 'gw_base_price' => 0.12, $qtyAttribute => 1],
                ['gw_id' => 4, 'gw_base_price' => 2.1, $qtyAttribute => 1],
                ['gw_id' => 5, 'gw_base_price' => 1, $qtyAttribute => 2],
            ];

            $items = [];

            foreach ($originalItems as $originalItemData) {
                $mock = $this->createPartialMock($contract, []);
                $mock->setData($originalItemData);
                if (isset($originalItemData['parent_item'])) {
                    $mock->setParentItem($this->createPartialMock($contract, []));
                }
                $items[] = new \Magento\Framework\DataObject(['original_item' =>  $mock]);
            }

            $salesModelDataVariations = [
                ['gw_id' => 1, 'gw_base_price' => null],
                ['gw_id' => 1, 'gw_base_price' => 0],
                ['gw_id' => 1, 'gw_base_price' => 0.12],
            ];

            foreach ($salesModelDataVariations as $salesModelData) {
                // cart with no items: 0
                $data[] = [[], $salesModelData, 0 + (float) $salesModelData['gw_base_price']];
                // cart with items: 4.22 = 1 * 0.12 + 1 * 2.1 + 2 * 1
                $data[] = [$items, $salesModelData, 4.22 + (float) $salesModelData['gw_base_price']];
            }
        }

        return $data;
    }
}
