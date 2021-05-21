<?php
namespace Eway\EwayRapid\Block\Adminhtml;

use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Mycard extends \Magento\Backend\Block\Template implements TabInterface
{
    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    /** @codingStandardsIgnoreLine @var ManagerInterface */
    protected $tokenManager;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        ManagerInterface $tokenManager,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->config = $config;
        $this->tokenManager = $tokenManager;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Saved Cards (eWAY)');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Saved Cards (eWAY)');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->config->getValue('token_enabled');
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }

    public function getListingAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('ewayrapid/mycards/index', ['customer_id' => $this->getCustomerId()]);
    }

    // @codingStandardsIgnoreLine
    protected function getCustomerId()
    {
        if ($customer = $this->tokenManager->getCurrentCustomer()) {
            return $customer->getId();
        }

        return '';
    }
}
