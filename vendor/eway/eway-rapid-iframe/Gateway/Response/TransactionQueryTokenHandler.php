<?php
namespace Eway\IFrame\Gateway\Response;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class TransactionQueryTokenHandler implements HandlerInterface
{
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();

        $tokenId = $response[Config::TOKEN_CUSTOMER_ID];
        $payment->setAdditionalInformation(Config::TOKEN_CUSTOMER_ID, $tokenId);
    }
}
