<?php
namespace Eway\EwayRapid\Gateway\Http;

use Eway\EwayRapid\Gateway\Request\BuilderComposite;
use Eway\EwayRapid\Model\Config\Source\Mode;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    /** @var TransferBuilder */
    protected $transferBuilder; // @codingStandardsIgnoreLine

    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
    
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    public function create(array $request)
    {
        $method = null;

        if (isset($request[BuilderComposite::METHOD])) {
            $method = $request[BuilderComposite::METHOD];
            unset($request[BuilderComposite::METHOD]);
        };

        return $this->transferBuilder
            ->setBody($request)
            ->setMethod($method)
            ->setAuthUsername($this->getApiKey())
            ->setAuthPassword($this->getApiPassword())
            ->setUri($this->getApiEndPoint())
            ->build();
    }

    protected function getApiKey() // @codingStandardsIgnoreLine
    {
        return $this->isSandbox() ?
            $this->config->getValue('sandbox_api_key') :
            $this->config->getValue('live_api_key');
    }

    protected function getApiPassword() // @codingStandardsIgnoreLine
    {
        return $this->isSandbox() ?
            $this->config->getValue('sandbox_api_password') :
            $this->config->getValue('live_api_password');
    }

    protected function getApiEndPoint() // @codingStandardsIgnoreLine
    {
        return $this->isSandbox() ?
            $this->config->getValue('sandbox_endpoint') :
            $this->config->getValue('live_endpoint');
    }

    protected function isSandbox() // @codingStandardsIgnoreLine
    {
        return $this->config->getValue('mode') == Mode::SANDBOX;
    }
}
