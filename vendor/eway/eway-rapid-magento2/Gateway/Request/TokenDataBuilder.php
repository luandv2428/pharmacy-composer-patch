<?php
namespace Eway\EwayRapid\Gateway\Request;

use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class TokenDataBuilder implements BuilderInterface
{
    /** @var ManagerInterface */
    protected $tokenManager; // @codingStandardsIgnoreLine

    public function __construct(ManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $payment = $payment->getPayment();
        if ($tokenId = $payment->getAdditionalInformation(Config::TOKEN_ID)) {
            $tokenCustomerId = $this->tokenManager->getCustomerTokenId($tokenId);
            if ($tokenCustomerId) {
                return [
                    Config::CUSTOMER => [
                        Config::TOKEN_CUSTOMER_ID => $tokenCustomerId
                    ]
                ];
            }
        }

        return [];
    }
}
