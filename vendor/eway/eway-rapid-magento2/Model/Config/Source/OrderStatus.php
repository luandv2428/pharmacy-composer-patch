<?php
namespace Eway\EwayRapid\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class OrderStatus extends \Magento\Sales\Model\Config\Source\Order\Status\Processing
{
    const STATUS_EWAY_AUTHORISED = 'eway_authorised';
    const STATUS_EWAY_CAPTURED   = 'eway_captured';

    /** @codingStandardsIgnoreLine @var ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($orderConfig);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Filter out order status based on eWAY requirement
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        $paymentAction = $this->scopeConfig->getValue('payment/ewayrapid/payment_action');

        foreach ($options as $key => $option) {
            if ($paymentAction == PaymentAction::ACTION_AUTHORIZE_CAPTURE) {
                if ($option['value'] == self::STATUS_EWAY_AUTHORISED) {
                    unset($options[$key]);
                }
            } else {
                if ($option['value'] == self::STATUS_EWAY_CAPTURED) {
                    unset($options[$key]);
                }
            }
        }

        return $options;
    }
}
