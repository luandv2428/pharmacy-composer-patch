<?php
namespace Eway\EwayRapid\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentAction implements ArrayInterface
{
    const ACTION_AUTHORIZE         = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorise and Capture')
            ],
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => __('Authorise Only')
            ]
        ];
    }
}
