<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class PaymentDataBuilder extends \Eway\EwayRapid\Gateway\Request\AbstractDataBuilder
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $order = $payment->getOrder();

        $result = [
            Config::TOTAL_AMOUNT  => sprintf('%.2F', SubjectReader::readAmount($buildSubject)) * 100,
            Config::CURRENCY_CODE => $order->getCurrencyCode()
        ];

        if ($this->config->getValue('invoice_reference')) {
            $result[Config::INVOICE_REFERENCE] = $order->getOrderIncrementId();
            $result[Config::INVOICE_NUMBER]    = $order->getOrderIncrementId();
        }

        if ($this->config->getValue('invoice_description')) {
            $result[Config::INVOICE_DESCRIPTION] = $this->getInvoiceDescription($order);
        }

        return [Config::PAYMENT => $result];
    }

    protected function getInvoiceDescription(OrderAdapterInterface $order) //@codingStandardsIgnoreLine
    {
        $itemsDescription = [];
        foreach ($order->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item instanceof OrderItemInterface) {
                $itemsDescription[] = sprintf('%s x %s', $item->getQtyOrdered(), $item->getName());
            } elseif ($item instanceof CartItemInterface) {
                $itemsDescription[] = sprintf('%s x %s', $item->getQty(), $item->getName());
            }
        }
        $description = implode(', ', $itemsDescription);
        return strlen($description) > 64 ? substr($description, 0, 61) . '...' : $description;
    }
}
