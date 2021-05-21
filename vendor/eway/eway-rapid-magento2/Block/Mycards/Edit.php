<?php
namespace Eway\EwayRapid\Block\Mycards;

use Eway\EwayRapid\Model\Ui\ConfigProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Edit extends \Magento\Directory\Block\Data
{
    /** @codingStandardsIgnoreLine @var ConfigProvider */
    protected $configProvider;

    /** @codingStandardsIgnoreLine @var Registry */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        Registry $registry,
        ConfigProvider $configProvider,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->registry       = $registry;
        $this->configProvider = $configProvider;
    }

    public function getCurrentToken()
    {
        return $this->registry->registry('current_token');
    }

    public function getSaveTokenUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/save');
    }

    public function getCardBlock()
    {
        $methodConfig = $this->configProvider->getActiveMethodConfig();
        return $this->getLayout()->createBlock($methodConfig->getMycardFormBlock(), 'ewayrapid.mycard');
    }

    public function getJsConfig()
    {
        $token = $this->getCurrentToken();
        $config = $this->configProvider->getConfig();
        $config['payment'][ConfigProvider::CODE]['is_editing'] = (bool) $token;
        $config['payment'][ConfigProvider::CODE]['selected_token'] = $token ? $token->getId() : 0;
        return json_encode($config);
    }

    public function getRegionId($countryCode, $regionCode)
    {
        if ($this->directoryHelper->isRegionRequired($countryCode)) {
            $regionData = $this->directoryHelper->getRegionData();
            $regionData = $regionData[$countryCode];
            foreach ($regionData as $regionId => $region) {
                if ($region['code'] == $regionCode) {
                    return $regionId;
                }
            }
        }

        return '';
    }
}
