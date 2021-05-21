<?php
namespace Eway\IFrame\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class TransactionQueryDataBuilder implements BuilderInterface
{
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        return [Config::ACCESS_CODE => $paymentModel->getAdditionalInformation(Config::ACCESS_CODE)];
    }
}
