<?php
namespace Eway\IFrame\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class TokenQueryDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        $tokenId = $paymentModel->getAdditionalInformation(Config::TOKEN_CUSTOMER_ID);
        $paymentModel->setAdditionalInformation(Config::TOKEN_CUSTOMER_ID, null);
        return [Config::TOKEN_CUSTOMER_ID => $tokenId];
    }
}
