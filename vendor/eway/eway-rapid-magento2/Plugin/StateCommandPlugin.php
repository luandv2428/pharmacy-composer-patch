<?php
namespace Eway\EwayRapid\Plugin;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;

class StateCommandPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    // @codingStandardsIgnoreLine
    public function aroundExecute(
        CommandInterface $subject,
        \Closure $proceed,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {
        $result = $proceed($payment, $amount, $order);
        if ($payment->getMethod() == \Eway\EwayRapid\Model\Ui\ConfigProvider::CODE
                && !$payment->getAlreadyCapturedTransaction()) {
            // Do not update status in case fraud is detected
            if ($payment->getIsFraudDetected()) {
                return $result;
            }
            $orderStatus = $this->config->getValue('order_status');
            if ($orderStatus && $order->getState() == Order::STATE_PROCESSING) {
                $order->setStatus($orderStatus);
            }
        }

        return $result;
    }
}
