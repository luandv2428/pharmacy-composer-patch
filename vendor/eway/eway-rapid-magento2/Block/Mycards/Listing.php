<?php
namespace Eway\EwayRapid\Block\Mycards;

use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Gateway\ConfigInterface;

class Listing extends \Magento\Framework\View\Element\Template
{
    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    /** @codingStandardsIgnoreLine @var ManagerInterface */
    protected $tokenManager;

    public function __construct(
        Template\Context $context,
        ManagerInterface $tokenManager,
        ConfigInterface $config,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->config = $config;
        $this->tokenManager = $tokenManager;
    }

    public function getActiveTokensList()
    {
        return $this->tokenManager->getActiveTokenList();
    }

    public function getDefaultToken()
    {
        return $this->tokenManager->getDefaultToken();
    }

    public function canEditToken()
    {
        return $this->config->getValue('can_edit_token');
    }

    public function getEditTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl('*/*/edit', ['id' => $id]);
    }

    public function getCreateTokenUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/create');
    }

    public function getDeleteTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl('*/*/delete', ['id' => $id]);
    }

    public function getSetDefaultTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl('*/*/setDefault', ['id' => $id]);
    }
}
