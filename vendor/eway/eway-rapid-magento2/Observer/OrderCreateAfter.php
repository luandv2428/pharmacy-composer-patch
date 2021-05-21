<?php
namespace Eway\EwayRapid\Observer;

use Eway\EwayRapid\Model\Config;
use Magento\Framework\Event\Observer;

class OrderCreateAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Eway\EwayRapid\Model\OrderFactory
     */
    protected $ewayOrderFactory; // @codingStandardsIgnoreLine

    public function __construct(\Eway\EwayRapid\Model\OrderFactory $ewayOrderFactory)
    {
        $this->ewayOrderFactory = $ewayOrderFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ewayOrder = $this->ewayOrderFactory->create();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        if ($payment->getMethod() == \Eway\EwayRapid\Model\Ui\ConfigProvider::CODE) {
            $ewayOrder
                ->setOrderId($order->getId())
                ->setBeagleScore($payment->getAdditionalInformation(Config::BEAGLE_SCORE))
                ->setFraudAction($payment->getAdditionalInformation(Config::FRAUD_ACTION))
                ->setTransactionCaptured($payment->getAdditionalInformation(Config::TRANSACTION_CAPTURED))
                ->setShouldVerify($payment->getIsFraudDetected())
                ->setItemsOrdered($this->getItemsOrders($order))
                ->setTransactionId($payment->getAdditionalInformation('transaction_id'));

            $fraudMessages = $payment->getAdditionalInformation('fraud_messages');
            $formattedfraudMessages = [];
            if (!empty($fraudMessages)) {
                foreach ($fraudMessages as $messageCode) {
                    $messageText = \Eway\Rapid::getMessage($messageCode);
                    if ($messageText == $messageCode) {
                        $messageText = 'Unknown message';
                    }
                    $formattedfraudMessages[] = sprintf('%s: %s', $messageCode, $messageText);
                }
            }
            if (!empty($formattedfraudMessages)) {
                $ewayOrder->setFraudMessages(implode("\n", $formattedfraudMessages));
                $historyItem = $order->getStatusHistoryCollection()->getFirstItem(); //@codingStandardsIgnoreLine
                if ($historyItem) {
                    $historyItem->setComment(
                        $historyItem->getComment() . '<br/>Reason:<br/>' . implode("<br/>", $formattedfraudMessages)
                    )->save();
                }
            }

            $ewayOrder->save();
        }
    }

    protected function getItemsOrders(\Magento\Sales\Model\Order $order) // @codingStandardsIgnoreLine
    {
        $output = [];
        /** @var \Magento\Sales\Api\Data\OrderItemInterface[] $items */
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $output[] = sprintf('%dx %s', $item->getQtyOrdered(), $item->getName());
        }

        return implode("\n", $output);
    }
}
