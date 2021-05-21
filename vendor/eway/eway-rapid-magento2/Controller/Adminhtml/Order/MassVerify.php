<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Order;

use Eway\EwayRapid\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Ui\Component\MassAction\Filter;

class MassVerify extends AbstractMassAction
{
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $orderIds = $this->getRequest()->getParam('order_id');
        if ($orderIds) {
            // From eWAY Order grid
            try {
                $collection = $this->collectionFactory->create()->addFieldToFilter('order_id', ['in' => $orderIds]);
                return $this->massAction($collection);
            } catch (\Magento\Framework\Exception\PaymentException $e) {
                $this->messageManager->addErrorMessage(
                    __('Error happened when verifying order, likely because transaction not found.')
                );
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($this->redirectUrl);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($this->redirectUrl);
            }
        } else {
            // From Sales Order grid
            $this->redirectUrl = 'sales/order/';
            return parent::execute();
        }
    }

    public function massAction(AbstractCollection $collection)
    {
        $summary = [];
        /** @var \Eway\EwayRapid\Model\Order $ewayOrder */
        foreach ($collection as $ewayOrder) {
            $result = $ewayOrder->verifyTransaction();
            if ($result) {
                $summary[$result] = isset($summary[$result]) ? ($summary[$result] + 1) : 1;
            }
        }
        if (empty($summary)) {
            $this->messageManager->addWarningMessage(__('No eWAY order need to be verified.'));
        } else {
            array_walk(
                $summary,
                function (&$count, $status) {
                    $count = sprintf('%s %s', $count, $status);
                }
            );
            $this->messageManager->addSuccessMessage(
                __('Selected eWAY order(s) has been verified successfully: %1.', implode(', ', $summary))
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($this->redirectUrl);
    }
}
