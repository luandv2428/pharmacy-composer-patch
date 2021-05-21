<?php
namespace Eway\IFrame\Controller\IFrame;

use Eway\IFrame\Model\GetAccessCodeTransactionService;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;

class GetAccessCode extends Action
{
    /** @codingStandardsIgnoreLine @var Session */
    protected $checkoutSession;

    /** @codingStandardsIgnoreLine @var GetAccessCodeTransactionService */
    protected $getAccessCodeTransactionService;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        GetAccessCodeTransactionService $getAccessCodeTransactionService
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->getAccessCodeTransactionService = $getAccessCodeTransactionService;
    }

    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getBillingAddress()->getEmail() && $this->getRequest()->getParam('GuestEmail')) {
            $quote->getBillingAddress()->setEmail($this->getRequest()->getParam('GuestEmail'));
        }
        return $this->getAccessCodeTransactionService->process($quote, $this->getRequest());
    }
}
