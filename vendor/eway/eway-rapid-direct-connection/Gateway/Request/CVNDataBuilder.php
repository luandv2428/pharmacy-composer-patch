<?php
namespace Eway\DirectConnection\Gateway\Request;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CVNDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        return [
            Config::CUSTOMER => [
                Config::CARD_DETAILS => [
                    Config::CARD_CVN => $paymentModel->getAdditionalInformation(Config::CARD_CVN),
                ]
            ]
        ];
    }
}
