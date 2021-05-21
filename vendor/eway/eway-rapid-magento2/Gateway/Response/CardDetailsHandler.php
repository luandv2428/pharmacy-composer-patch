<?php
namespace Eway\EwayRapid\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Eway\EwayRapid\Model\Config\Source\Cctype;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use \Eway\EwayRapid\Model\Config;

class CardDetailsHandler implements HandlerInterface
{
    /**
     * Config
     *
     * @var Cctype
     */
    protected $sourceCCtype; // @codingStandardsIgnoreLine

    /**
     * Constructor
     *
     * @param Cctype $sourceCCtype
     */
    public function __construct(Cctype $sourceCCtype)
    {
        $this->sourceCCtype = $sourceCCtype;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        if (isset($response[Config::CUSTOMER]) && isset($response[Config::CUSTOMER][Config::CARD_DETAILS])) {
            $cardDetails = $response[Config::CUSTOMER][Config::CARD_DETAILS];

            if (!empty($cardDetails[Config::CARD_NUMBER])) {
                $payment->setData('cc_type', Config::getCardType($cardDetails[Config::CARD_NUMBER]));
                $payment->setData('cc_last_4', substr($cardDetails[Config::CARD_NUMBER], -4));
            }

            if (!empty($cardDetails[Config::CARD_EXPIRY_MONTH]) && !empty($cardDetails[Config::CARD_EXPIRY_YEAR])) {
                $payment->setData('cc_exp_month', $cardDetails[Config::CARD_EXPIRY_MONTH]);
                $payment->setData('cc_exp_year', '20' . $cardDetails[Config::CARD_EXPIRY_YEAR]);
            }
        }
    }
}
