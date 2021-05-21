<?php
namespace Eway\EwayRapid\Model\Customer\Token;

use Magento\Customer\Model\Customer;

interface ManagerInterface
{
    /**
     * @return Customer
     */
    public function getCurrentCustomer();

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCurrentCustomer(Customer $customer);

    /**
     * Get eWAY Customer Token Id
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @return string
     */
    public function getCustomerTokenId($id);

    /**
     * Get token object by id (id used in Magento, not Customer Token Id)
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @return \Eway\EwayRapid\Model\Customer\Token
     */
    public function getTokenById($id);

    /**
     * Get last token id of this customer
     *
     * @return bool | int
     */
    public function getLastTokenId();

    /**
     * Add new token to customer's token list
     *
     * @param array $info
     */
    public function addToken($info);

    /**
     * Update token identified by id
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @param array $info
     */
    public function updateToken($id, $info);

    /**
     * Delete token identified by id
     *
     * @param int $id id used in Magento, not Customer Token Id
     */
    public function deleteToken($id);

    /**
     * Set token identified by id as default token
     *
     * @param int $id id used in Magento, not Customer Token Id
     */
    public function setDefaultToken($id);

    /**
     * Get default token id
     *
     * @return bool | int
     */
    public function getDefaultToken();

    /**
     * Get active token list of current customer
     *
     * @return array
     */
    public function getActiveTokenList();
}
