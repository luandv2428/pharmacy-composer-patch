<?php
namespace Eway\SecureFields\Model\Ui;

use Eway\EwayRapid\Model\Config\Source\Mode;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\ConfigInterface;

class Config implements \Eway\EwayRapid\Model\Ui\MethodSpecificConfigInterface
{
    const CONNECTION_TYPE = 'securefields';

    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    /** @var CommandPool */
    protected $commandPool; // @codingStandardsIgnoreLine

    public function __construct(ConfigInterface $config, CommandPool $commandPool)
    {
        $this->config = $config;
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
        return 'Secure Fields';
    }

    /**
     * @return array
     */
    public function getMethodConfig()
    {
        return [
            'fieldStyles'    => $this->config->getValue('field_styles'),
            'publicApiKey'   => $this->getPublicApiKey(),
        ];
    }

    protected function getPublicApiKey() // @codingStandardsIgnoreLine
    {
        return $this->config->getValue('mode') == Mode::SANDBOX ?
            $this->config->getValue('sandbox_public_api_key') :
            $this->config->getValue('live_public_api_key');
    }

    /**
     * @return string;
     */
    public function getMethodRendererPath()
    {
        return 'Eway_SecureFields/js/view/payment/method-renderer/securefields';
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
        return 'EwayRapidSecureFieldsMycardForm';
    }
}
