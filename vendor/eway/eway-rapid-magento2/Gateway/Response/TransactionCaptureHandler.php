<?php
namespace Eway\EwayRapid\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use \Eway\EwayRapid\Model\Config;

/**
 * Class TransactionCaptureHandler
 */
class TransactionCaptureHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setTransactionId($response[Config::TRANSACTION_ID]);
        $orderPayment->setAdditionalInformation('transaction_id', $response[Config::TRANSACTION_ID]);

        $orderPayment->setIsTransactionClosed(false);
        $orderPayment->setShouldCloseParentTransaction(true);
    }
}
