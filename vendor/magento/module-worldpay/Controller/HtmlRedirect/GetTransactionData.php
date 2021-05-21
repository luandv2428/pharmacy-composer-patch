<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Worldpay\Controller\HtmlRedirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Worldpay\Model\Api\PlaceTransactionService;

/**
 * Class GetTransactionData.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @deprecated 100.3.5 Starting from Magento 2.3.5 WorldPay payment method core integration is deprecated
 *       in favor of official payment integration available on the marketplace
 */
class GetTransactionData extends Action
{
    /**
     * @var PlaceTransactionService
     */
    private $placeTransactionService;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PlaceTransactionService $placeTransactionService
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        PlaceTransactionService $placeTransactionService,
        Session $checkoutSession
    ) {
        $this->placeTransactionService = $placeTransactionService;
        $this->session = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Execute method.
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $orderId = $this->session->getData('last_order_id');

        if (!is_numeric($orderId)) {
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            return $resultJson->setData(['message' => __('No such order id.')]);
        }

        $response = $this->placeTransactionService->placeTransaction($orderId);
        $resultJson->setData($response);
        return $resultJson;
    }
}
