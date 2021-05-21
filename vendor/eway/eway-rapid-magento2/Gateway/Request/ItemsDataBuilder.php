<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use \Eway\EwayRapid\Model\Config;

class ItemsDataBuilder extends AbstractDataBuilder
{
    public function build(array $buildSubject)
    {
        if (!$this->config->getValue('transfer_cart_items')) {
            return [];
        }

        $payment = SubjectReader::readPayment($buildSubject);

        $order = $payment->getOrder();

        if ($order->getItems()) {
            $amount = SubjectReader::readAmount($buildSubject);
            $items = $this->buildItems($order, $amount);
            return [Config::ITEMS => $items];
        } else {
            return [];
        }
    }

    /**
     * @param $order \Magento\Payment\Gateway\Data\OrderAdapterInterface
     * @param $amount float
     * @return array
     */
    protected function buildItems($order, $amount) // @codingStandardsIgnoreLine
    {
        $result = [];
        $itemsTotal = 0;
        /** @var \Magento\Sales\Model\Order\Item[] $items */
        $items = $order->getItems();
        foreach ($items as $item) {
            $result[] = [
                Config::SKU         => $item->getSku(),
                Config::DESCRIPTION => $item->getName(),
                Config::QUANTITY    => $item->getQty(),
                Config::UNIT_COST   => (int) round(100 * $item->getBasePrice()),
                Config::TAX         => (int) round(100 * $item->getBaseTaxAmount()),
                Config::TOTAL       => (int) round(100 * $item->getBaseRowTotalInclTax())
            ];
            $itemsTotal += $item->getBaseRowTotalInclTax();
        }

        if ($order instanceof \Eway\EwayRapid\Gateway\QuoteAdapter) {
            if ($order->getShippingAmount() > 0) {
                $result[] = [
                    Config::DESCRIPTION => 'Shipping',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => (int) round(100 * $order->getShippingAmount()),
                    Config::TAX         => (int) round(100 * $order->getShippingTaxAmount()),
                    Config::TOTAL       => (int) round(100 * $order->getShippingAmountInclTax())
                ];
                $itemsTotal += $order->getShippingAmountInclTax();
            }

            if ($order->getDiscountAmount() < 0) {
                $result[] = [
                    Config::DESCRIPTION => 'Discount',
                    Config::QUANTITY    => 1,
                    Config::UNIT_COST   => (int) round(100 * $order->getDiscountAmount()),
                    Config::TOTAL       => (int) round(100 * $order->getDiscountAmount())
                ];
                $itemsTotal += $order->getDiscountAmount();
            }
        }

        // Make sure the items total always match amount.
        if ($itemsTotal != $amount) {
            $adjustment = (int) round(100 * ($amount - $itemsTotal));
            $result[] = [
                Config::DESCRIPTION => 'Adjustment',
                Config::QUANTITY    => 1,
                Config::UNIT_COST   => $adjustment,
                Config::TOTAL       => $adjustment
            ];
        }

        return $result;
    }
}
