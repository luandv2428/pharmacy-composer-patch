<?php
namespace Eway\TransparentRedirect\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\CcConfig;

class Config extends \Eway\IFrame\Model\Ui\Config
{
    const CONNECTION_TYPE       = 'transparent';

    /**
     * @var CcConfig
     */
    protected $ccConfig; //@codingStandardsIgnoreLine

    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlBuilder,
        CommandPool $commandPool,
        CcConfig $ccConfig
    ) {
        parent::__construct($config, $urlBuilder, $commandPool);
        $this->ccConfig = $ccConfig;
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
        return 'Transparent Redirect';
    }

    /**
     * @return string;
     */
    public function getMethodRendererPath()
    {
        return 'Eway_TransparentRedirect/js/view/payment/method-renderer/transparent';
    }

    /**
     * @return string
     */
    public function getMycardFormBlock()
    {
        return 'EwayRapidTransparentMycardForm';
    }

    public function getMethodConfig()
    {
        $config = parent::getMethodConfig();
        $visaCheckoutApiKey = $this->config->getValue('visa_checkout_apikey');
        $config = array_merge($config, [
            'enable_paypal'        => $this->config->getValue('enable_paypal'),
            'enable_masterpass'    => $this->config->getValue('enable_masterpass'),
            'enable_visa_checkout' => $this->config->getValue('enable_visa_checkout') && !empty($visaCheckoutApiKey),
            'enable_amex_checkout' => $this->config->getValue('enable_amex_checkout'),
            'masterpass_image_url' => $this->ccConfig->getViewFileUrl('Eway_TransparentRedirect::images/masterpass_button.png'), //@codingStandardsIgnoreLine
            'paypal_image_url'     => $this->ccConfig->getViewFileUrl('Eway_TransparentRedirect::images/paypal_button.png'), //@codingStandardsIgnoreLine
            'visa_image_url'       => $this->ccConfig->getViewFileUrl('Eway_TransparentRedirect::images/visa_checkout.png'), //@codingStandardsIgnoreLine
            'visa_sdk_url'         => \Eway\EwayRapid\Model\Config::getVisaCheckoutSdkUrl($this->config->getValue('mode')), //@codingStandardsIgnoreLine
            'visa_checkout_apikey' => $visaCheckoutApiKey,
        ]);

        return $config;
    }
}
