<?php
namespace Eway\EwayRapid\Model\Customer;

use Magento\Framework\DataObject;
use Zend\Stdlib\JsonSerializable;

abstract class JsonSerializableAbstract extends DataObject implements JsonSerializable
{
    /**
     * Recursively serialize json for all value in _data array in DataObject
     *
     * @param array $rawData
     * @return array
     */
    public function getJsonData(array $rawData = null)
    {
        if ($rawData === null) {
            $rawData = $this->_data;
        }

        $jsonData = [];
        foreach ($rawData as $key => $value) {
            if (is_scalar($value)) {
                $jsonData[$key] = $value;
            } elseif (is_array($value)) {
                $jsonData[$key] = $this->getJsonData($value);
            } elseif (is_object($value) && $value instanceof JsonSerializable) {
                $jsonData[$key] = $value->getJsonData();
            }
        }

        return $jsonData;
    }

    public function jsonSerialize()
    {
        return json_encode($this->getJsonData());
    }

    /**
     * Override \Magento\Framework\DataObject::_underscore() to prevent transform of field name.
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name) // @codingStandardsIgnoreLine
    {
        return $name;
    }
}
