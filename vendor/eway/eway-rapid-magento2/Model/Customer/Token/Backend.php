<?php
namespace Eway\EwayRapid\Model\Customer\Token;

use Eway\EwayRapid\Model\Customer\SavedTokens;
use Eway\EwayRapid\Model\Customer\SavedTokensFactory;
use Magento\Framework\Encryption\EncryptorInterface;

class Backend extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /** @codingStandardsIgnoreLine @var EncryptorInterface */
    protected $encryptor;

    /** @codingStandardsIgnoreLine @var SavedTokensFactory */
    protected $savedTokensFactory;

    public function __construct(EncryptorInterface $encryptor, SavedTokensFactory $savedTokensFactory)
    {
        $this->encryptor = $encryptor;
        $this->savedTokensFactory = $savedTokensFactory;
    }

    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $savedTokens = $object->getData($attrCode);
        /* @codingStandardsIgnoreLine @var SavedTokens $savedTokens */
        if ($savedTokens && $savedTokens instanceof SavedTokens) {
            $object->setData('save_tokens_object_backup', $savedTokens);
            $object->setData($attrCode, $this->encryptor->encrypt($savedTokens->jsonSerialize()));
        }

        $object->setAttributeSetId(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);

        return $this;
    }

    public function afterSave($object)
    {
        // Revert to object representation after save
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($backupObj = $object->getData('save_tokens_object_backup')) {
            $object->setData($attrCode, $backupObj);
        }

        return $this;
    }

    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $savedTokens = $this->savedTokensFactory->create();
        if ($encryptedJson = $object->getData($attrCode)) {
            $object->setData($attrCode, $savedTokens->decodeJSON($this->encryptor->decrypt($encryptedJson)));
        } else {
            $object->setData($attrCode, $savedTokens);
        }

        return $this;
    }
}
