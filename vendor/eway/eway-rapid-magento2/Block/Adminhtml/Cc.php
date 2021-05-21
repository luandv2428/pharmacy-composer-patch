<?php
namespace Eway\EwayRapid\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Cc extends \Magento\Payment\Block\Form\Cc
{
    /** @var \Eway\EwayRapid\Model\Ui\ConfigProvider */
    protected $configProvider; // @codingStandardsIgnoreLine

    protected $_template = 'Eway_EwayRapid::form/cc.phtml'; // @codingStandardsIgnoreLine
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Eway\EwayRapid\Model\Ui\ConfigProvider $configProvider,
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper,
        array $data = []
    ) {
    
        parent::__construct($context, $paymentConfig, $data);
        $this->configProvider = $configProvider;
        $this->moduleHelper = $moduleHelper;
    }

    public function getJsConfig()
    {
        return json_encode($this->configProvider->getConfig());
    }

    public function toHtml()
    {
        if (!$this->moduleHelper->isActive()) {
            return '';
        }

        return parent::toHtml();
    }
}
