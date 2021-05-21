<?php
namespace Eway\EwayRapid\Block\Adminhtml\Mycards;

class Listing extends \Eway\EwayRapid\Block\Mycards\Listing
{
    public function canEditToken()
    {
        return true;
    }

    public function getEditTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl(
            'ewayrapid/mycards/edit',
            ['id' => $id, 'customer_id' => $this->getCustomerId()]
        );
    }

    public function getCreateTokenUrl()
    {
        return $this->_urlBuilder->getUrl(
            'ewayrapid/mycards/create',
            ['customer_id' => $this->getCustomerId()]
        );
    }

    public function getDeleteTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl(
            'ewayrapid/mycards/delete',
            ['id' => $id, 'customer_id' => $this->getCustomerId()]
        );
    }

    public function getSetDefaultTokenUrl($id)
    {
        return $this->_urlBuilder->getUrl(
            'ewayrapid/mycards/setDefault',
            ['id' => $id, 'customer_id' => $this->getCustomerId()]
        );
    }

    // @codingStandardsIgnoreLine
    protected function getCustomerId()
    {
        if ($customer = $this->tokenManager->getCurrentCustomer()) {
            return $customer->getId();
        }

        return '';
    }
}
