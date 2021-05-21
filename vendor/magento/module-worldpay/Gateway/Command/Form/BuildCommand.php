<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Gateway\Command\Form;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Worldpay\Gateway\Request\HtmlRedirect\OrderDataBuilder;

/**
 * Class BuildCommand
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class BuildCommand implements CommandInterface
{
    /**
     * @var OrderDataBuilder
     */
    private $builder;

    /**
     * @var ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param OrderDataBuilder $builder
     * @param ArrayResultFactory $arrayResultFactory
     * @param Logger $logger
     */
    public function __construct(
        OrderDataBuilder $builder,
        ArrayResultFactory $arrayResultFactory,
        Logger $logger
    ) {
        $this->builder = $builder;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        $result = $this->builder->build($commandSubject);

        $this->logger->debug(['payment_form_data' => $result]);

        return $this->arrayResultFactory->create(['array' => $result]);
    }
}
