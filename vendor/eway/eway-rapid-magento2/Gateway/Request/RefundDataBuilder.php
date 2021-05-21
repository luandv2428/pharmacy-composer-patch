<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class RefundDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            Config::REFUND => [
                Config::TRANSACTION_ID => $paymentDO->getPayment()->getAdditionalInformation('transaction_id'),
                Config::TOTAL_AMOUNT => sprintf('%.2F', SubjectReader::readAmount($buildSubject)) * 100
            ]
        ];
    }
}
