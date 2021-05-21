<?php
namespace Eway\DirectConnection\Model\Ui;

use Eway\EwayRapid\Model\Config\Source\Mode;
use Eway\EwayRapid\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\CcGenericConfigProvider;

class Config implements \Eway\EwayRapid\Model\Ui\MethodSpecificConfigInterface
{
    const CONNECTION_TYPE = 'direct';

    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    /** @var CommandPool */
    protected $commandPool; // @codingStandardsIgnoreLine

    /** @var CcGenericConfigProvider */
    protected $ccConfigProvider; // @codingStandardsIgnoreLine
    /**
     * @var \Magento\Payment\Model\CcConfig
     */
    protected $ccConfig; // @codingStandardsIgnoreLine

    public function __construct(
        ConfigInterface $config,
        CommandPool $commandPool,
        CcGenericConfigProvider $ccConfigProvider,
        \Magento\Payment\Model\CcConfig $ccConfig
    ) {
        $this->config           = $config;
        $this->commandPool      = $commandPool;
        $this->ccConfigProvider = $ccConfigProvider;
        $this->ccConfig         = $ccConfig;
    }

    /**
     * @return string
     */
    public function getConnectionType()
    {
        return self::CONNECTION_TYPE;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Direct Connection';
    }

    /**
     * @return array
     */
    public function getMethodConfig()
    {
        $config = $this->ccConfigProvider->getConfig();
        $visaCheckoutApiKey = $this->config->getValue('visa_checkout_apikey');

        return [
            'encryptionKey'  => $this->getEncryptionKey(),
            'availableTypes' => $this->getCcConfig($config, 'availableTypes'),
            'months'         => $this->getCcConfig($config, 'months'),
            'years'          => $this->getCcConfig($config, 'years'),
            'enable_visa_checkout' => $this->config->getValue('enable_visa_checkout') && !empty($visaCheckoutApiKey),
            'visa_image_url'       => $this->ccConfig->getViewFileUrl('Eway_TransparentRedirect::images/visa_checkout.png'), //@codingStandardsIgnoreLine
            'visa_sdk_url'         => \Eway\EwayRapid\Model\Config::getVisaCheckoutSdkUrl($this->config->getValue('mode')), //@codingStandardsIgnoreLine
            'visa_checkout_apikey' => $visaCheckoutApiKey,
        ];
    }

    protected function getCcConfig($config, $field) // @codingStandardsIgnoreLine
    {
        if ($config && isset($config['payment']['ccform'][$field][ConfigProvider::CODE])) {
            return $config['payment']['ccform'][$field][ConfigProvider::CODE];
        } else {
            return '';
        }
    }

    protected function getEncryptionKey() // @codingStandardsIgnoreLine
    {
        return $this->config->getValue('mode') == Mode::SANDBOX ?
            $this->config->getValue('sandbox_encryption_key') :
            $this->config->getValue('live_encryption_key');
    }

    /**
     * @return string;
     */
    public function getMethodRendererPath()
    {
        return 'Eway_DirectConnection/js/view/payment/method-renderer/direct';
    }

    /**
     * @return \Magento\Payment\Gateway\Command\CommandPool
     */
    public function getCommandPool()
    {
        return $this->commandPool;
    }

    /**
     * @return string
     */
    public function getMycardFormBlock()
    {
        return 'EwayRapidDirectMycardForm';
    }
}
