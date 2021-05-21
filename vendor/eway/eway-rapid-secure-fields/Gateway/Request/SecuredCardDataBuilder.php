<?php
namespace Eway\SecureFields\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class SecuredCardDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        return [Config::SECURED_CARD_DATA => $paymentModel->getAdditionalInformation(Config::SECURED_CARD_DATA)];
    }
}
