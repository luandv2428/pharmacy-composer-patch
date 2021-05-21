<?php
namespace Eway\EwayRapid\Model;

use Magento\Framework\DataObject;

class DummyPaymentInfo extends DataObject implements \Magento\Payment\Model\InfoInterface
{
    protected $addtionalInformation = []; // @codingStandardsIgnoreLine

    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return $data;
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return $data;
    }

    /**
     * Set Additional information about payment into Payment model
     *
     * @param string $key
     * @param string|null $value
     * @return mixed
     */
    public function setAdditionalInformation($key, $value = null)
    {
        $this->addtionalInformation[$key] = $value;
        return $this;
    }

    /**
     * Check whether there is additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     */
    public function hasAdditionalInformation($key = null)
    {
        return isset($this->addtionalInformation[$key]);
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($this->hasAdditionalInformation($key)) {
            unset($this->addtionalInformation[$key]);
        }
        return $this;
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return mixed
     */
    public function getAdditionalInformation($key = null)
    {
        return $this->hasAdditionalInformation($key) ? $this->addtionalInformation[$key] : null;
    }

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethodInstance()
    {
        return null;
    }
}
