<?php
namespace Eway\DirectConnection\Gateway\Request;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CardDetailsDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        if ($secureCardData = $paymentModel->getAdditionalInformation(Config::SECURED_CARD_DATA)) {
            return [
                Config::SECURED_CARD_DATA => $secureCardData
            ];
        } else {
            return [
                Config::CUSTOMER => [
                    Config::CARD_DETAILS => [
                        Config::CARD_NAME         => $paymentModel->getAdditionalInformation(Config::CARD_NAME),
                        Config::CARD_NUMBER       => $paymentModel->getAdditionalInformation(Config::CARD_NUMBER),
                        Config::CARD_EXPIRY_MONTH => str_pad(
                            $paymentModel->getAdditionalInformation(Config::CARD_EXPIRY_MONTH),
                            2,
                            '0',
                            STR_PAD_LEFT
                        ),
                        Config::CARD_EXPIRY_YEAR  => $paymentModel->getAdditionalInformation(Config::CARD_EXPIRY_YEAR),
                        Config::CARD_CVN          => $paymentModel->getAdditionalInformation(Config::CARD_CVN),
                    ]
                ]
            ];
        }
    }
}
