<?php
namespace Eway\EwayRapid\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;

class OrderService
{
    const STATUS_REVIEW   = 'In Review';
    const STATUS_DENIED   = 'Denied';
    const STATUS_APPROVED = 'Approved';
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var OrderFactory
     */
    private $ewayOrderFactory;

    public function __construct(ObjectManagerInterface $objectManager, OrderFactory $ewayOrderFactory)
    {
        $this->objectManager = $objectManager;
        $this->ewayOrderFactory = $ewayOrderFactory;
    }

    public function verifyFraudStatus(Payment $payment, Invoice $invoice = null)
    {
        if ($payment->getIsTransactionPending()) {
            return self::STATUS_REVIEW;
        } elseif (!$payment->getAdditionalInformation(Config::TRANSACTION_CAPTURED)) {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            if ($invoice) {
                $invoice->cancel();
                $invoice->getOrder()->cancel();
                $invoice->getOrder()
                    ->addStatusHistoryComment('Order cancelled because transaction has been denied in MYeWAY.');

                /** @var \Magento\Framework\DB\Transaction $transaction */
                $transaction = $this->objectManager->create( //@codingStandardsIgnoreLine
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );

                $ewayOrder = $this->ewayOrderFactory->create()->load($invoice->getOrderId());
                if ($ewayOrder->getId()) {
                    $ewayOrder
                        ->setFraudAction(self::STATUS_DENIED)
                        ->setTransactionCaptured(false)
                        ->setShouldVerify(false);
                    $transaction->addObject($ewayOrder);
                }

                $transaction->save();
            }

            return self::STATUS_DENIED;
        }

        return self::STATUS_APPROVED;
    }
}
