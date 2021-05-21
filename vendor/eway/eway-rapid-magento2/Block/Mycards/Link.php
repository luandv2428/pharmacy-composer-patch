<?php
namespace Eway\EwayRapid\Block\Mycards;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /** @codingStandardsIgnoreLine @var \Magento\Payment\Gateway\ConfigInterface */
    protected $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Payment\Gateway\ConfigInterface $config,
        array $data = []
    ) {
    
        parent::__construct($context, $defaultPath, $data);
        $this->config = $config;
    }

    public function toHtml()
    {
        return $this->config->getValue('token_enabled') ? parent::toHtml() : '';
    }
}
