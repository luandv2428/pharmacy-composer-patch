<?php
namespace Eway\EwayRapid\Gateway\Request;

use Eway\EwayRapid\Model\Config\Source\PaymentAction;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use \Eway\EwayRapid\Model\Config;

class BaseRequestDataBuilder extends AbstractDataBuilder
{
    /** @var string */
    protected $transactionType; // @codingStandardsIgnoreLine
    /**
     * @var \Eway\EwayRapid\Model\Version
     */
    private $version;

    public function __construct(
        ConfigInterface $config,
        \Eway\EwayRapid\Model\Version $version,
        $transactionType = Config::PURCHASE
    ) {
        parent::__construct($config);
        $this->transactionType = $transactionType;
        $this->version = $version;
    }

    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $order = $payment->getOrder();
        $shouldCapture =
            $this->config->getValue('payment_action', $order->getStoreId()) == PaymentAction::ACTION_AUTHORIZE_CAPTURE;

        $deviceId = sprintf('%s - eWAY %s', $this->version->getMagentoVersion(), $this->version->getEwayVersion());
        $deviceId = strlen($deviceId) > 50 ? substr($deviceId, 0, 50) : $deviceId;
        $result = [
            Config::CUSTOMER_IP      => $order->getRemoteIp(),
            Config::TRANSACTION_TYPE => $this->transactionType,
            Config::CAPTURE          => $shouldCapture,
            Config::CUSTOMER_READ_ONLY => true,
            Config::DEVICE_ID        => $deviceId
        ];

        if ($this->config->getValue('connection_type') == 'sharedpage') {
            if ($this->config->getValue('sharedpage_theme')) {
                $result['CustomView'] = $this->config->getValue('sharedpage_theme');
            }

            if ($this->config->getValue('beagle_verify_email')) {
                $result['VerifyCustomerEmail'] = true;
            }

            if ($this->config->getValue('beagle_verify_phone')) {
                $result['VerifyCustomerPhone'] = true;
            }
        }

        if ($this->config->getValue('token_enabled', $order->getStoreId())) {
            $paymentDO = $payment->getPayment();
            if ($paymentDO->getAdditionalInformation(Config::TOKEN_ACTION) == Config::TOKEN_ACTION_NEW) {
                $result[Config::SAVE_CUSTOMER] = true;
            }
        }

        return $result;
    }
}
