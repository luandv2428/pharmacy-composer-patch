<?php
namespace Eway\EwayRapid\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use \Eway\EwayRapid\Model\Config;

/**
 * Class ResponseMessagesHandler
 */
class ResponseMessagesHandler implements HandlerInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $messages = str_replace(' ', '', $response[Config::RESPONSE_MESSAGE]);
        $messages = explode(',', $messages);

        $fraudMessages = preg_grep('/^F.*/', $messages);
        if (!empty($fraudMessages)) {
            $payment->setIsTransactionPending(true);
            $payment->setIsFraudDetected(true);
            $this->eventManager->dispatch('ewayrapid_fraud_detected', ['payment' => $payment]);
            $payment->setAdditionalInformation('fraud_messages', $fraudMessages);
        }

        $additionalFields = [Config::FRAUD_ACTION, Config::BEAGLE_SCORE, Config::TRANSACTION_CAPTURED];
        foreach ($additionalFields as $field) {
            if (isset($response[$field])) {
                $payment->setAdditionalInformation($field, $response[$field]);
            }
        }
    }
}
