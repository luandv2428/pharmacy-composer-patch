<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class TransactionIdDataBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            Config::TRANSACTION_ID => $paymentDO->getPayment()->getAdditionalInformation('transaction_id')
        ];
    }
}
