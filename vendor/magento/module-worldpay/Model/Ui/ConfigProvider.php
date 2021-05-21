<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class ConfigProvider implements ConfigProviderInterface
{
    const WORLDPAY_CODE = 'worldpay';
    const TRANSACTION_DATA_URL = 'worldpay/htmlredirect/gettransactiondata';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::WORLDPAY_CODE => [
                    'transactionDataUrl' => $this->urlBuilder->getUrl(self::TRANSACTION_DATA_URL, ['_secure' => true])
                ]
            ]
        ];
    }
}
