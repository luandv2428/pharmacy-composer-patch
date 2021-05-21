<?php
namespace Eway\IFrame\Model\Ui;

use Eway\EwayRapid\Model\Config\Source\PaymentAction;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\ConfigInterface;

class Config implements \Eway\EwayRapid\Model\Ui\MethodSpecificConfigInterface
{
    const CONNECTION_TYPE = 'iframe';

    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    /** @codingStandardsIgnoreLine @var UrlInterface */
    protected $urlBuilder;

    /** @codingStandardsIgnoreLine @var CommandPool */
    protected $commandPool;

    public function __construct(ConfigInterface $config, UrlInterface $urlBuilder, CommandPool $commandPool)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->commandPool = $commandPool;
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
        return 'IFrame';
    }

    /**
     * @return array
     */
    public function getMethodConfig()
    {
        return [
            'get_access_code_url'
                    => $this->urlBuilder->getUrl('ewayrapid/iFrame/getAccessCode', ['_secure' => true]),
            'mycard_get_access_code_url'
                    => $this->urlBuilder->getUrl('ewayrapid/mycard/getAccessCode', ['_secure' => true]),
            // In IFrame and Shared Page, create token only possible with capture
            'can_create_token' => ($this->config->getValue('payment_action') == PaymentAction::ACTION_AUTHORIZE_CAPTURE)
        ];
    }

    /**
     * @return string;
     */
    public function getMethodRendererPath()
    {
        return 'Eway_IFrame/js/view/payment/method-renderer/iframe';
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
        return 'EwayRapidIFrameMycardForm';
    }
}
