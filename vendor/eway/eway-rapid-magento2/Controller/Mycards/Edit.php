<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Gateway\ConfigInterface;

class Edit extends AbstractMycards
{
    /** @codingStandardsIgnoreLine @var Registry */
    protected $registry;

    public function __construct(
        Context $context,
        Session $customerSession,
        ConfigInterface $config,
        PageFactory $pageFactory,
        ManagerInterface $tokenManager,
        Registry $registry
    ) {
    
        parent::__construct($context, $customerSession, $config, $pageFactory, $tokenManager);
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

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set($token ?
            __('Edit Credit Card') : __('New Credit Card'));

        return $resultPage;
    }
}
