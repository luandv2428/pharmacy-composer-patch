<?php
namespace Eway\EwayRapid\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    const SANDBOX = 'sandbox';
    const LIVE    = 'live';

    public function toOptionArray()
    {
        return [
            [
                'value' => 'sandbox',
                'label' => 'Sandbox'
            ],
            [
                'value' => 'live',
                'label' => 'Live'
            ],
        ];
    }
}
