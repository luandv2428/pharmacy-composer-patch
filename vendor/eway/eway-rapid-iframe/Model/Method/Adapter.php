<?php
namespace Eway\IFrame\Model\Method;

use Eway\EwayRapid\Model\Config;

class Adapter extends \Magento\Payment\Model\Method\Adapter
{
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if (($addtionalData = $data->getAdditionalData()) && isset($addtionalData[Config::ACCESS_CODE])) {
            $this->getInfoInstance()->setAdditionalInformation(
                Config::ACCESS_CODE,
                $addtionalData[Config::ACCESS_CODE]
            );
        }

        return $this;
    }
}
