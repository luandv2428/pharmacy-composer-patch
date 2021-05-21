<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class TransferFactory
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setClientConfig(
                [
                    'timeout' => 60,
                    'verifypeer' => 1
                ]
            )
            ->setBody($request)
            ->setMethod(\Zend_Http_Client::POST)
            ->setUri(
                (bool)$this->config->getValue('sandbox_flag')
                ? $this->config->getValue('iadmin_url_test')
                : $this->config->getValue('iadmin_url')
            )
            ->build();
    }
}
