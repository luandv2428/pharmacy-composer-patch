<?php
namespace Eway\EwayRapid\Test\Unit\Gateway\Request;

use Eway\EwayRapid\Model\Config;

class ItemsDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $orderMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $paymentDO;
    private $config;
    /**
     * @var \Eway\EwayRapid\Gateway\Request\ItemsDataBuilder
     */
    private $itemsDataBuilder;

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(\Eway\EwayRapid\Gateway\QuoteAdapter::class)->disableOriginalConstructor()->getMock();
        $this->paymentDO = $this->getMockForAbstractClass(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentDO->method('getOrder')->willReturn($this->orderMock);
        $this->config = $this->getMockForAbstractClass(\Magento\Payment\Gateway\ConfigInterface::class);
        $this->config->method('getValue')->willReturnMap([
            ['transfer_cart_items', null, true],
        ]);
        $this->itemsDataBuilder = new \Eway\EwayRapid\Gateway\Request\ItemsDataBuilder($this->config);

    }

    public function testBuildNoItems()
    {
        $this->config->method('getValue')->willReturnMap([
            ['transfer_cart_items', null, false],
        ]);

        $this->assertEquals([], $this->itemsDataBuilder->build(['amount' => 0, 'payment' => $this->paymentDO]));
    }

    public function testBuildSimpleItems()
    {
        $this->orderMock->method('getItems')->willReturn([
            $this->getMockItem('SKU1', 'Item 1', 1, 100.0, 10.0, 110.0),
            $this->getMockItem('SKU2', 'Item 2', 2, 200.0, 40.0, 440.0),
        ]);

        $buildSubject = [
            'amount' => 550,
            'payment' => $this->paymentDO,
        ];

        $result = $this->itemsDataBuilder->build($buildSubject);
        $this->assertEquals([
            Config::ITEMS => [
                [
                    Config::SKU         => 'SKU1',
                    Config::DESCRIPTION => 'Item 1',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => 10000,
                    Config::TAX         => 1000,
                    Config::TOTAL       => 11000
                ],
                [
                    Config::SKU         => 'SKU2',
                    Config::DESCRIPTION => 'Item 2',
                    Config::QUANTITY    => 2,
                    Config::UNIT_COST   => 20000,
                    Config::TAX         => 4000,
                    Config::TOTAL       => 44000
                ],
            ]
        ], $result);
    }

    public function testBuildComplexItems()
    {
        $this->orderMock->method('getItems')->willReturn([
            $this->getMockItem('SKU1', 'Item 1', 1, 100.0, 10.0, 110.0),
            $this->getMockItem('SKU2', 'Item 2', 2, 200.0, 40.0, 440.0),
        ]);

        $this->orderMock->method('getShippingAmount')->willReturn(70);
        $this->orderMock->method('getShippingTaxAmount')->willReturn(7);
        $this->orderMock->method('getShippingAmountInclTax')->willReturn(77);

        $this->orderMock->method('getDiscountAmount')->willReturn(-50);

        $buildSubject = [
            'amount' => 577,
            'payment' => $this->paymentDO,
        ];

        $result = $this->itemsDataBuilder->build($buildSubject);
        $this->assertEquals([
            Config::ITEMS => [
                [
                    Config::SKU         => 'SKU1',
                    Config::DESCRIPTION => 'Item 1',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => 10000,
                    Config::TAX         => 1000,
                    Config::TOTAL       => 11000
                ],
                [
                    Config::SKU         => 'SKU2',
                    Config::DESCRIPTION => 'Item 2',
                    Config::QUANTITY    => 2,
                    Config::UNIT_COST   => 20000,
                    Config::TAX         => 4000,
                    Config::TOTAL       => 44000
                ],
                [
                    Config::DESCRIPTION => 'Shipping',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => 7000,
                    Config::TAX         => 700,
                    Config::TOTAL       => 7700
                ],
                [
                    Config::DESCRIPTION => 'Discount',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => -5000,
                    Config::TOTAL       => -5000
                ]
            ],

        ], $result);
    }

    public function testBuildItemAdjustment()
    {
        $this->orderMock->method('getItems')->willReturn([
            $this->getMockItem('SKU1', 'Item 1', 1, 100.0, 10.0, 110.0),
            $this->getMockItem('SKU2', 'Item 2', 2, 200.0, 40.0, 440.0),
        ]);

        $buildSubject = [
            'amount' => 570,
            'payment' => $this->paymentDO,
        ];

        $result = $this->itemsDataBuilder->build($buildSubject);
        $this->assertEquals([
            Config::ITEMS => [
                [
                    Config::SKU         => 'SKU1',
                    Config::DESCRIPTION => 'Item 1',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => 10000,
                    Config::TAX         => 1000,
                    Config::TOTAL       => 11000
                ],
                [
                    Config::SKU         => 'SKU2',
                    Config::DESCRIPTION => 'Item 2',
                    Config::QUANTITY    => 2,
                    Config::UNIT_COST   => 20000,
                    Config::TAX         => 4000,
                    Config::TOTAL       => 44000
                ],
                [
                    Config::DESCRIPTION => 'Adjustment',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => 2000,
                    Config::TOTAL       => 2000
                ]
            ]
        ], $result);
    }

    protected function getMockItem($sku, $name, $qty, $basePrice, $baseTax, $rowTotalInclTax)
    {
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'getName', 'getBasePrice', 'getBaseTaxAmount', 'getBaseRowTotalInclTax'])
            ->getMock();
        $itemMock->method('getSku')->willReturn($sku);
        $itemMock->method('getName')->willReturn($name);
        $itemMock->method('getBasePrice')->willReturn($basePrice);
        $itemMock->method('getBaseTaxAmount')->willReturn($baseTax);
        $itemMock->method('getBaseRowTotalInclTax')->willReturn($rowTotalInclTax);
        $itemMock->setData('qty', $qty);
        return $itemMock;
    }
}