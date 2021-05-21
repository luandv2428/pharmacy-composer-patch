<?php
namespace Eway\EwayRapid\Gateway\Request;

use \Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

class CustomerTokenDataBuilder extends CustomerDataBuilder
{
    /** @var ManagerInterface */
    protected $tokenManager; // @codingStandardsIgnoreLine

    public function __construct(ManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function build(array $buildSubject)
    {
        $result =  parent::build($buildSubject);
        $result = $result[Config::CUSTOMER];

        $paymentDO = SubjectReader::readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        if ($tokenId = $payment->getAdditionalInformation(Config::TOKEN_ID)) {
            $tokenCustomerId = $this->tokenManager->getCustomerTokenId($tokenId);
            if ($tokenCustomerId) {
                $result[Config::TOKEN_CUSTOMER_ID] = $tokenCustomerId;
            }
        }

        $billingAddress = $paymentDO->getOrder()->getBillingAddress();
        if ($billingAddress instanceof DataObject) {
            $result[Config::JOB_DESCRIPTION] = $billingAddress->getJobDescription();
            $result[Config::MOBILE]          = $billingAddress->getMobile();
            $result[Config::EMAIL]           = $billingAddress->getEmail();
            $result[Config::FAX]             = $billingAddress->getFax();
        }

        return $result;
    }
}
