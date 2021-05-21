<?php
namespace Eway\DirectConnection\Gateway\Command;

use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class AuthorizeStrategyCommand implements CommandInterface
{
    const CREATE_TOKEN        = 'create_token';
    const UPDATE_TOKEN        = 'update_token';
    const NON_TOKEN_AUTHORIZE = 'non_token_authorize';
    const CHARGE_TOKEN        = 'charge_token';
    const CHARGE_TOKEN_MOTO   = 'charge_token_moto';

    /** @var Command\CommandPoolInterface */
    protected $commandPool; // @codingStandardsIgnoreLine

    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    /** @var ManagerInterface */
    protected $tokenManager; // @codingStandardsIgnoreLine

    /**
     * @param Command\CommandPoolInterface $commandPool
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config,
        ManagerInterface $tokenManager
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
        $this->tokenManager = $tokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        if ($this->config->getValue('token_enabled')
            && $action = $paymentInfo->getAdditionalInformation(Config::TOKEN_ACTION)
        ) {
            switch ($action) {
                case Config::TOKEN_ACTION_NEW:
                    $this->commandPool->get(self::CREATE_TOKEN)->execute($commandSubject);
                    try {
                        // Must use MOTO since eWAY does not allow to use SecureCardData twice at the moment
                        return $this->commandPool->get(self::CHARGE_TOKEN_MOTO)->execute($commandSubject);
                    } catch (\Exception $e) {
                        // In case charge token failed for any reason, do not keep newly created token.
                        if ($tokenId = $paymentInfo->getAdditionalInformation(Config::TOKEN_ID)) {
                            $this->tokenManager->deleteToken($tokenId);
                        }
                        throw $e;
                    }
                    break;
                case Config::TOKEN_ACTION_UPDATE:
                    $this->commandPool->get(self::UPDATE_TOKEN)->execute($commandSubject);
                    // Must use MOTO since eWAY does not allow to use SecureCardData twice at the moment
                    return $this->commandPool->get(self::CHARGE_TOKEN_MOTO)->execute($commandSubject);

                case Config::TOKEN_ACTION_USE:
                    return $this->commandPool->get(self::CHARGE_TOKEN)->execute($commandSubject);
            }
        }

        return $this->commandPool->get(self::NON_TOKEN_AUTHORIZE)->execute($commandSubject);
    }
}
