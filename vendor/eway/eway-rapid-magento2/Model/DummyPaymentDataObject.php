<?php
namespace Eway\EwayRapid\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;

class DummyPaymentDataObject implements \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
{
    protected $order; // @codingStandardsIgnoreLine
    protected $payment; // @codingStandardsIgnoreLine

    public function __construct(
        DataObject $order,
        InfoInterface $payment
    ) {
        $this->order = $order;
        $this->payment = $payment;
    }

    /**
     * Returns order
     *
     * @return DataObject
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns payment
     *
     * @return InfoInterface
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
