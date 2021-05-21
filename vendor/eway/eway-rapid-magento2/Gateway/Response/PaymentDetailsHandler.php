<?php
namespace Eway\EwayRapid\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use \Eway\EwayRapid\Model\Config;

/**
 * Class PaymentDetailsHandler
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var array
     */
    private $additionalInformationMapping = [
        'transaction_type' => Config::TRANSACTION_TYPE,
        'transaction_id'   => Config::TRANSACTION_ID,
        'response_code'    => Config::RESPONSE_CODE,
        'beagle_verification' => Config::BEAGLE_VERIFICATION
    ];

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $payment->setTransactionId($response[Config::TRANSACTION_ID]);
        $payment->setLastTransId($response[Config::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(false);

        foreach ($this->additionalInformationMapping as $informationKey => $responseKey) {
            if (isset($response[$responseKey])) {
                $payment->setAdditionalInformation($informationKey, $response[$responseKey]);
            }
        }
    }
}
