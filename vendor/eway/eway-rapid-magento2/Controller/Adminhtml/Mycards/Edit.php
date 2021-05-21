<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Payment\Gateway\ConfigInterface;

class Edit extends AbstractMyCards
{
    /** @codingStandardsIgnoreLine @var \Magento\Framework\Registry */
    protected $registry;

    public function __construct(
        Action\Context $context,
        ConfigInterface $config,
        ManagerInterface $tokenManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry
    ) {
    
        parent::__construct($context, $config, $tokenManager, $layoutFactory, $resultJsonFactory, $resultLayoutFactory);
        $this->registry = $registry;
    }

    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $token = null;
        if ($this->getRequest()->getParam('id')) {
            $id = $this->getTokenId();
            $token = $this->tokenManager->getTokenById($id);
        }
        $this->registry->register('current_token', $token);

        return $this->resultLayoutFactory->create();
    }
}
