<?php
namespace Eway\EwayRapid\Model\Config\Source;

use Eway\EwayRapid\Model\Ui\ConfigProvider;
use Magento\Framework\Option\ArrayInterface;

class ConnectionType implements ArrayInterface
{
    /** @codingStandardsIgnoreLine @var ConfigProvider */
    protected $configProvider;

    /** @codingStandardsIgnoreLine @var  array */
    protected $optionArray = [];

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->optionArray) {
            $methodConfig = $this->configProvider->getAllMethodConfig();
            foreach ($methodConfig as $type => $config) {
                $this->optionArray[] = [
                    'value' => $type,
                    'label' => $config['label'],
                ];
            }
        }

        return $this->optionArray;
    }
}
