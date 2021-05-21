<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class AcceptValidator
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class AcceptValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);

        $isValid = true;
        $fails = [];

        $statements = [
            [
                $paymentDO->getOrder()->getCurrencyCode() === $response['authCurrency'],
                __('Currency doesn\'t match.')
            ],
            [
                sprintf('%.2F', $paymentDO->getOrder()->getGrandTotalAmount())
                === $response['authCost'],
                __('Amount doesn\'t match.')
            ],
            [
                in_array($response['authMode'], ['A', 'E']),
                __('Not supported response.')
            ]
        ];

        foreach ($statements as $statementResult) {
            if (!$statementResult[0]) {
                $isValid = false;
                $fails[] = $statementResult[1];
            }
        }

        return $this->createResult($isValid, $fails);
    }
}
