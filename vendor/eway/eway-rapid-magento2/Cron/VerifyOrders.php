<?php
namespace Eway\EwayRapid\Cron;

use Eway\EwayRapid\Model\Order;
use Eway\EwayRapid\Model\OrderFactory;
use Psr\Log\LoggerInterface;

class VerifyOrders
{
    /**
     * @var OrderFactory
     */
    private $ewayOrderFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(OrderFactory $ewayOrderFactory, LoggerInterface $logger)
    {
        $this->ewayOrderFactory = $ewayOrderFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $collection = $this->ewayOrderFactory->create()->getCollection()->filterOrdersThatNeedToBeVerified();
        $count = $collection->count(); //@codingStandardsIgnoreLine
        if (!$count) {
            $this->logger->debug('No order need to be verified.');
        } else {
            $this->logger->debug("Start verifying {$count} order(s):");
            /** @var Order $ewayOrder */
            foreach ($collection as $ewayOrder) {
                //@codingStandardsIgnoreLine
                $message = "Order id={$ewayOrder->getMagentoOrder()->getIncrementId()}, transaction id={$ewayOrder->getTransactionId()} ... ";
                try {
                    $result = $ewayOrder->verifyTransaction();
                    $message .= $result;
                } catch (\Exception $e) {
                    $message .= $e->getMessage() . "\nException = " . $e->getTraceAsString();
                }
                $this->logger->debug($message);
            }
            $this->logger->debug("Done.");
        }
    }
}
