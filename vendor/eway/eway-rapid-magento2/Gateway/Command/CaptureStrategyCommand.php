<?php
namespace Eway\EwayRapid\Gateway\Command;

use Eway\EwayRapid\Model\OrderService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

class CaptureStrategyCommand implements CommandInterface
{
    const SALE              = 'sale';
    const PRE_AUTH_CAPTURE  = 'pre_auth_capture';
    const QUERY_TRANSACTION = 'query_transaction';

    /**
     * @var Command\CommandPoolInterface
     */
    protected $commandPool; // @codingStandardsIgnoreLine
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry; // @codingStandardsIgnoreLine
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager; // @codingStandardsIgnoreLine
    /**
     * @var OrderService
     */
    protected $orderService; // @codingStandardsIgnoreLine

    /**
     * @param Command\CommandPoolInterface $commandPool
     * @param \Magento\Framework\Registry $registry
     * @param ObjectManagerInterface $objectManager
     * @param OrderService $orderService
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool,
        \Magento\Framework\Registry $registry,
        ObjectManagerInterface $objectManager,
        OrderService $orderService
    ) {
        $this->commandPool = $commandPool;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->orderService = $orderService;
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

        if ($paymentInfo->getAlreadyCapturedTransaction()) {
            // Do nothing, just let Magento continue normal capture flow.
            return null;
        }

        if ($this->registry->registry('ewayrapid_should_verify_fraud')) {
            $this->commandPool->get(self::QUERY_TRANSACTION)->execute($commandSubject);

            $fraudVerifyResult = $this->orderService->verifyFraudStatus(
                $paymentInfo,
                $this->registry->registry('current_invoice')
            );

            switch ($fraudVerifyResult) {
                case OrderService::STATUS_REVIEW:
                    throw new LocalizedException(__('Transaction still need to be reviewed in MYeWAY.'));

                case OrderService::STATUS_DENIED:
                    throw new LocalizedException(
                        __('Transaction has been denied in MYeWAY, this order has been cancelled')
                    );

                case OrderService::STATUS_APPROVED:
                    // Do nothing, just let Magento continue normal capture flow.
            }

            return null;
        }

        if ($paymentInfo instanceof Order\Payment
            && $paymentInfo->getAuthorizationTransaction()
        ) {
            return $this->commandPool
                ->get(self::PRE_AUTH_CAPTURE)
                ->execute($commandSubject);
        }

        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
