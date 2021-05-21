<?php
namespace Eway\EwayRapid\Model\Config\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    // @codingStandardsIgnoreLine
    protected $specificCardTypesList = [
        'VE' => 'Visa Electron',
        'ME' => 'Maestro',
    ];

    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DN', 'JCB', 'VE', 'ME'];
    }

    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        foreach ($this->specificCardTypesList as $code => $name) {
            $options[] = ['value' => $code, 'label' => $name];
        }

        return $options;
    }

    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }
}
