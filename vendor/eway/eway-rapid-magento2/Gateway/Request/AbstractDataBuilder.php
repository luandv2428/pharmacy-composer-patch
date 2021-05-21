<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

abstract class AbstractDataBuilder implements BuilderInterface
{
    /** @var ConfigInterface */
    protected $config; // @codingStandardsIgnoreLine

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
}
