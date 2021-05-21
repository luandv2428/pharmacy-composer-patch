<?php
namespace Eway\DirectConnection\Model\Method;

use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Config\Source\Mode;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Quote\Api\Data\CartInterface;

class Adapter extends \Magento\Payment\Model\Method\Adapter
{
    /** @var \Magento\Payment\Gateway\ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        \Magento\Payment\Gateway\ConfigInterface $config,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->config = $config;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $addtionalFields = [
            Config::TOKEN_ACTION, Config::TOKEN_ID,
            Config::CARD_NUMBER, Config::CARD_CVN, Config::CARD_NAME,
            Config::CARD_EXPIRY_MONTH, Config::CARD_EXPIRY_YEAR, Config::SECURED_CARD_DATA
        ];
        $addtionalData = $data->getAdditionalData();

        foreach ($addtionalFields as $field) {
            if (isset($addtionalData[$field])) {
                $this->getInfoInstance()->setAdditionalInformation($field, $addtionalData[$field]);
            }
        }

        return $this;
    }

    public function isAvailable(CartInterface $quote = null)
    {
        $storeId = $quote ? $quote->getStoreId() : null;
        $result = $this->config->getValue('mode', $storeId) == Mode::SANDBOX ?
            !empty($this->config->getValue('sandbox_encryption_key', $storeId)) :
            !empty($this->config->getValue('live_encryption_key', $storeId));

        return $result && parent::isAvailable($quote);
    }
}
