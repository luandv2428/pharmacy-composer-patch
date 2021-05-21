<?php
namespace Eway\IFrame\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use \Eway\EwayRapid\Model\Config;

class AuthorizeStrategyCommand implements CommandInterface
{
    const NON_TOKEN_AUTHORIZE    = 'non_token_authorize';
    const CREATE_TOKEN_AUTHORIZE = 'create_token_authorize';
    const QUERY_AND_CREATE_TOKEN = 'query_and_create_token';

    /** @var Command\CommandPoolInterface */
    protected $commandPool; // @codingStandardsIgnoreLine

    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    /**
     * @param Command\CommandPoolInterface $commandPool
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        ConfigInterface $config
    ) {
        $this->commandPool = $commandPool;
        $this->config = $config;
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

        if ($this->config->getValue('token_enabled')) {
            if ($action = $paymentInfo->getAdditionalInformation(Config::TOKEN_ACTION)) {
                switch ($action) {
                    case Config::TOKEN_ACTION_NEW:
                        $this->commandPool->get(self::CREATE_TOKEN_AUTHORIZE)->execute($commandSubject);
                        return $this->commandPool->get(self::QUERY_AND_CREATE_TOKEN)->execute($commandSubject);
                }
            }
        }

        return $this->commandPool->get(self::NON_TOKEN_AUTHORIZE)->execute($commandSubject);
    }
}
