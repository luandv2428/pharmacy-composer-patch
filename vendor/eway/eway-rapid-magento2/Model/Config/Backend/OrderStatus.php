<?php
namespace Eway\EwayRapid\Model\Config\Backend;

use Eway\EwayRapid\Model\Config\Source\PaymentAction;

class OrderStatus extends \Magento\Framework\App\Config\Value
{
    /**
     * Set default new order status when changing payment action field
     */
    public function beforeSave()
    {
        $paymentAction = $this->getFieldsetDataValue('payment_action');
        // Check if payment action is changed
        if ($paymentAction != $this->_config->getValue('payment/ewayrapid/payment_action')) {
            $defaultStatus = ( $paymentAction == PaymentAction::ACTION_AUTHORIZE_CAPTURE ?
                \Eway\EwayRapid\Model\Config\Source\OrderStatus::STATUS_EWAY_CAPTURED :
                \Eway\EwayRapid\Model\Config\Source\OrderStatus::STATUS_EWAY_AUTHORISED
            );

            $this->setValue($defaultStatus);
        };

        return $this;
    }
}
