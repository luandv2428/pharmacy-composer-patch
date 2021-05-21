<?php
namespace Eway\DirectConnection\Gateway\Response;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

class ResponseMessagesHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    const QUERY_TRANSACTION = 'query_transaction';

    /**
     * @var CommandPool
     */
    private $commandPool;

    public function __construct(CommandPool $commandPool)
    {
        $this->commandPool = $commandPool;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $payment->setAdditionalInformation('transaction_id', $response[Config::TRANSACTION_ID]);

        // Need to execute query transaction command since the create transaction does not return FraudAction, etc...
        $this->commandPool->get(self::QUERY_TRANSACTION)->execute($handlingSubject);
    }
}
