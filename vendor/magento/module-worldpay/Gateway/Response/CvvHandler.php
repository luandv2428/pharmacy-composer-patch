<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Response;

/**
 * Class CvvHandler
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class CvvHandler extends AvsHandler
{
    /**
     * @var array
     */
    protected static $codesPosition = [
        0 => 'cvv_result'
    ];

    /**
     * @var string
     */
    const FRAUD_CASE = 'cvv_fraud_case';
}
