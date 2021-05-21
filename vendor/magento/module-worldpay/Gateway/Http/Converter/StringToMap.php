<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Worldpay\Gateway\Http\Converter;

use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;

/**
 * Class StringToMap
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class StringToMap implements ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        if (!is_string($response)) {
            throw new ConverterException(__('The response type is incorrect. Verify the type and try again.'));
        }

        return explode(',', $response);
    }
}
