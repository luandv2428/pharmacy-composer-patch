<?php
namespace Eway\EwayRapid\Gateway\Response;

use Eway\EwayRapid\Model\Customer\CustomerInfoFactory;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use \Eway\EwayRapid\Model\Config;

class UpdateTokenHandler implements HandlerInterface
{
    /** @var ManagerInterface */
    protected $tokenManager; // @codingStandardsIgnoreLine

    /** @var CustomerInfoFactory */
    protected $customerInfoFactory; // @codingStandardsIgnoreLine

    public function __construct(ManagerInterface $tokenManager, CustomerInfoFactory $customerInfoFactory)
    {
        $this->tokenManager = $tokenManager;
        $this->customerInfoFactory = $customerInfoFactory;
    }

    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();

        if ($tokenId = $payment->getAdditionalInformation(Config::TOKEN_ID)) {
            $customer = $response[Config::CUSTOMER];
            $cardDetails = $customer[Config::CARD_DETAILS];
            $tokenInfo = [
                'Token'       => $customer[Config::TOKEN_CUSTOMER_ID],
                'Card'        => $cardDetails[Config::CARD_NUMBER],
                'Owner'       => $cardDetails[Config::CARD_NAME],
                'StartMonth'  => $cardDetails[Config::CARD_START_MONTH],
                'StartYear'   => $cardDetails[Config::CARD_START_YEAR],
                'IssueNumber' => $cardDetails[Config::CARD_ISSUE_NUMBER],
                'ExpMonth'    => $cardDetails[Config::CARD_EXPIRY_MONTH],
                'ExpYear'     => (strlen($cardDetails[Config::CARD_EXPIRY_YEAR]) == 2 ?
                    '20' . $cardDetails[Config::CARD_EXPIRY_YEAR] : $cardDetails[Config::CARD_EXPIRY_YEAR]),
                'Type'        => Config::getCardType($cardDetails[Config::CARD_NUMBER]),
            ];

            unset($customer[Config::TOKEN_CUSTOMER_ID]);
            unset($customer[Config::CARD_DETAILS]);
            $tokenInfo['Address'] = $this->customerInfoFactory->create()->addData($customer);

            $this->tokenManager->updateToken($tokenId, $tokenInfo);
        }
    }
}
