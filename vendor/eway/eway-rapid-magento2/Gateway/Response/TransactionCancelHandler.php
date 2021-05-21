<?php
namespace Eway\EwayRapid\Gateway\Response;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class TransactionCancelHandler implements HandlerInterface
{
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setTransactionId($response[Config::TRANSACTION_ID]);

        $orderPayment->setIsTransactionClosed(true);
        $orderPayment->setShouldCloseParentTransaction(true);
    }
}
