<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Command\Response;

use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CancelCommand
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class CancelCommand implements CommandInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagementInterface;

    /**
     * @param OrderManagementInterface $orderManagementInterface
     * @param Session $checkoutSession
     */
    public function __construct(
        OrderManagementInterface $orderManagementInterface,
        Session $checkoutSession
    ) {
        $this->orderManagementInterface = $orderManagementInterface;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            throw new \LogicException;
        }

        $this->orderManagementInterface->cancel($paymentDO->getOrder()->getId());

        $this->checkoutSession->setLastRealOrderId($paymentDO->getOrder()->getOrderIncrementId());
        $this->checkoutSession->restoreQuote();
    }
}
