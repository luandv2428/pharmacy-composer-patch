<?php
namespace Eway\EwayRapid\Controller\Adminhtml;

use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\ConfigInterface;

abstract class AbstractMyCards extends \Magento\Backend\App\Action
{
    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    /** @codingStandardsIgnoreLine @var ManagerInterface */
    protected $tokenManager;

    /** @codingStandardsIgnoreLine @var JsonFactory */
    protected $resultJsonFactory;

    /** @codingStandardsIgnoreLine @var LayoutFactory */
    protected $layoutFactory;

    /** @codingStandardsIgnoreLine @var \Magento\Framework\View\Result\LayoutFactory */
    protected $resultLayoutFactory;

    public function __construct(
        Action\Context $context,
        ConfigInterface $config,
        ManagerInterface $tokenManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
    
        parent::__construct($context);
        $this->config = $config;
        $this->tokenManager = $tokenManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    public function execute()
    {
        if (!$this->config->getValue('token_enabled')) {
            return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
        }

        try {
            return $this->_execute();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->error();
        }
    }

    // @codingStandardsIgnoreLine
    protected function getTokenId()
    {
        $id = $this->getRequest()->getParam('id');
        if (!is_numeric($id)) {
            throw new PaymentException(__('Invalid token id'));
        }

        return (int) $id;
    }

    // @codingStandardsIgnoreLine
    abstract protected function _execute();

    // @codingStandardsIgnoreLine
    protected function success()
    {
        return $this->ajaxResult(false);
    }

    // @codingStandardsIgnoreLine
    protected function error()
    {
        return $this->ajaxResult(true);
    }

    // @codingStandardsIgnoreLine
    protected function ajaxResult($error)
    {
        /** @codingStandardsIgnoreLine @var $block \Magento\Framework\View\Element\Messages */
        $block = $this->layoutFactory->create()->getMessagesBlock();

        $block->setMessages($this->messageManager->getMessages(true));
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'messages' => $block->getGroupedHtml(),
            'error' => $error
        ]);
    }
}
