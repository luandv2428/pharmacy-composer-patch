<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Command;

use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CaptureCommand
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class CaptureCommand extends GatewayCommand
{
    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return void
     * @throws \Exception
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        $payment = $paymentDO->getPayment();
        if (!$payment instanceof Payment) {
            return null;
        }

        if (!$payment->getAuthorizationTransaction()) {
            return null;
        }

        parent::execute($commandSubject);
    }
}
