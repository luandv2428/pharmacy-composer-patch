<?php
namespace Eway\IFrame\Gateway\Command;

use Eway\EwayRapid\Model\Config;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

class MyCardCreateUpdateTokenCommand implements \Magento\Payment\Gateway\CommandInterface
{
    const QUERY_ACCESS_CODE      = 'query_access_code';
    const QUERY_AND_CREATE_TOKEN = 'query_and_create_token';
    const QUERY_AND_UPDATE_TOKEN = 'query_and_update_token';

    /** @var CommandPoolInterface */
    protected $commandPool; // @codingStandardsIgnoreLine

    public function __construct(CommandPoolInterface $commandPool)
    {
        $this->commandPool = $commandPool;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $this->commandPool->get(self::QUERY_ACCESS_CODE)->execute($commandSubject);

        /** @var \Eway\EwayRapid\Model\DummyPaymentDataObject $paymentDO */
        $paymentDO = $commandSubject['payment'];
        if ($paymentDO->getPayment()->getAdditionalInformation(Config::TOKEN_ID)) {
            $this->commandPool->get(self::QUERY_AND_UPDATE_TOKEN)->execute($commandSubject);
        } else {
            $this->commandPool->get(self::QUERY_AND_CREATE_TOKEN)->execute($commandSubject);
        }
    }
}
