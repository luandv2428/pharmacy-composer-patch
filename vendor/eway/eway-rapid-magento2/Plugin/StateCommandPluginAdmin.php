<?php
namespace Eway\EwayRapid\Plugin;

use Magento\Backend\App\Action\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;

class StateCommandPluginAdmin
{
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var Context
     */
    private $context;

    public function __construct(ConfigInterface $config, Context $context)
    {
        $this->config = $config;
        $this->context = $context;
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
            && $this->context->getRequest()->getParam('ewayrapid_create_from_admin')
        ) {
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
