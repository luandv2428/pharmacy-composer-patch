<?php
namespace Eway\IFrame\Controller\Adminhtml\IFrame;

use Eway\IFrame\Model\GetAccessCodeTransactionService;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

class GetAccessCode extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /** @codingStandardsIgnoreLine @var GetAccessCodeTransactionService */
    protected $getAccessCodeTransactionService;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        GetAccessCodeTransactionService $getAccessCodeTransactionService
    ) {
    
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
        $this->getAccessCodeTransactionService = $getAccessCodeTransactionService;
    }

    public function execute()
    {
        $this->_initSession();
        $quote = $this->_getQuote();

        return $this->getAccessCodeTransactionService->process($quote, $this->getRequest());
    }
}
