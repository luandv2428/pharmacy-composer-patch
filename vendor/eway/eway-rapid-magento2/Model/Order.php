<?php
namespace Eway\EwayRapid\Model;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\OrderRepository;

/**
 * Class Order
 *
 * @method $this setOrderId(int $value)
 * @method int getOrderId()
 * @method $this setItemsOrdered(string $value)
 * @method string getItemsOrdered()
 * @method $this setBeagleScore(float $value)
 * @method float getBeagleScore()
 * @method $this setFraudAction(string $value)
 * @method string getFraudAction()
 * @method $this setTransactionCaptured(int $value)
 * @method int getTransactionCaptured()
 * @method $this setFraudMessages(string $value)
 * @method string getFraudMessages()
 * @method $this setShouldVerify(int $value)
 * @method int getShouldVerify()
 * @method $this setTransactionId(string $value)
 * @method string getTransactionId()
 *
 * @package Eway\EwayRapid\Model
 */
class Order extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'eway_ewayrapid_order';

    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var OrderService
     */
    private $ewayOrderService;
    /**
     * @var Ui\ConfigProvider
     */
    private $configProvider;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrderRepository $orderRepository,
        OrderService $ewayOrderService,
        \Eway\EwayRapid\Model\Ui\ConfigProvider $configProvider,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderRepository = $orderRepository;
        $this->ewayOrderService = $ewayOrderService;
        $this->configProvider = $configProvider;
        $this->objectManager = $objectManager;
        $this->moduleHelper = $moduleHelper;
    }

    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('Eway\EwayRapid\Model\ResourceModel\Order');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getMagentoOrder()
    {
        return $this->orderRepository->get($this->getId());
    }

    public function verifyTransaction()
    {
        if ($this->getShouldVerify() && $this->getTransactionId()) {
            /** @var \Magento\Sales\Model\Order $magentoOrder */
            $magentoOrder = $this->getMagentoOrder();
            $payment = $magentoOrder->getPayment();

            // @codingStandardsIgnoreLine
            $paymentDataObject = new \Eway\EwayRapid\Model\DummyPaymentDataObject($magentoOrder, $payment);
            $commandPool = $this->configProvider->getActiveMethodConfig()->getCommandPool();
            $commandPool->get('query_transaction')->execute(['payment' => $paymentDataObject]);

            /** @var Invoice $invoice */
            $invoice = null;
            if ($magentoOrder->getInvoiceCollection()->count()) { //@codingStandardsIgnoreLine
                $invoice = $magentoOrder->getInvoiceCollection()->getFirstItem(); //@codingStandardsIgnoreLine
            };

            $verifyResult = $this->ewayOrderService->verifyFraudStatus($payment, $invoice);
            if ($verifyResult == OrderService::STATUS_APPROVED) {
                /** @var \Magento\Framework\DB\Transaction $transaction */
                $transaction = $this->objectManager->create('Magento\Framework\DB\Transaction'); //@codingStandardsIgnoreLine
                if ($invoice) {
                    $payment->setAlreadyCapturedTransaction(true);
                    $invoice->capture();
                    $transaction->addObject($invoice)->addObject($magentoOrder);
                }

                if (! $magentoOrder->getCustomerIsGuest()) {
                    if ($this->moduleHelper->isCustomerBlocked($magentoOrder->getCustomerId())) {
                        $this->moduleHelper->unblockCustomer($magentoOrder->getCustomerId());
                    }
                }

                $this->setFraudAction(OrderService::STATUS_APPROVED)->setShouldVerify(false);
                $transaction->addObject($this)->save();
            }

            return $verifyResult;
        }

        return false;
    }
}
