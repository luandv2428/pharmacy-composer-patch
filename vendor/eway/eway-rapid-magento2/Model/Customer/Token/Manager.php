<?php
namespace Eway\EwayRapid\Model\Customer\Token;

use Eway\EwayRapid\Model\Customer\ProviderInterface;
use Eway\EwayRapid\Model\Customer\SavedTokens;
use Eway\EwayRapid\Model\Customer\SavedTokensFactory;
use Eway\EwayRapid\Model\Customer\Token;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\NotFoundException;

class Manager implements ManagerInterface
{
    // @codingStandardsIgnoreLine
    protected $currentCustomer = null;

    /** @codingStandardsIgnoreLine @var SavedTokensFactory */
    protected $savedTokensFactory;

    /** @codingStandardsIgnoreLine @var ProviderInterface */
    protected $customerProvider;

    public function __construct(
        SavedTokensFactory $savedTokensFactory,
        ProviderInterface $customerProvider
    ) {
    
        $this->savedTokensFactory = $savedTokensFactory;
        $this->customerProvider = $customerProvider;
    }

    /**
     * @return Customer
     */
    public function getCurrentCustomer()
    {
        if ($this->currentCustomer == null) {
            $this->currentCustomer = $this->customerProvider->getCurrentCustomer();
        }

        return $this->currentCustomer;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCurrentCustomer(Customer $customer)
    {
        $this->currentCustomer = $customer;
        return $this;
    }

    /**
     * Get eWAY Customer Token Id
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @return string
     */
    public function getCustomerTokenId($id)
    {
        $token = $this->getTokenById($id);
        return $token->getToken() ?
            $token->getToken() :
            $token->getTokenCustomerID();
    }

    /**
     * Get token object by id (id used in Magento, not Customer Token Id)
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @return Token
     */
    public function getTokenById($id)
    {
        $customer = $this->getCurrentCustomer();
        if ($customer && $customer->getSavedTokens()) {
            return $customer->getSavedTokens()->getTokenById($id);
        } else {
            throw new NotFoundException(__('Customer does not have any saved token.'));
        }
    }

    /**
     * Get last token id of this customer
     *
     * @return bool | int
     */
    public function getLastTokenId()
    {
        $customer = $this->getCurrentCustomer();
        if ($customer && $customer->getSavedTokens()) {
            return $customer->getSavedTokens()->getLastId();
        }

        return false;
    }

    /**
     * Add new token to customer's token list
     *
     * @param [] $info
     */
    public function addToken($info)
    {
        $tokenId = null;
        $customer = $this->getCurrentCustomer();
        if ($customer) {
            /** @var SavedTokens $savedTokens */
            $savedTokens = $customer->getSavedTokens();
            if (!$savedTokens) {
                $savedTokens = $this->savedTokensFactory->create();
            }

            $tokenId = $savedTokens->addToken($info);
            $customer->setSavedTokens($savedTokens);

            // Only save existed customer, new customer will be saved by Magento.
            if ($customer->getId()) {
                $customer->save();
            }
        }

        return $tokenId;
    }

    /**
     * Update token identified by id
     *
     * @param int $id id used in Magento, not Customer Token Id
     * @param [] $info
     */
    public function updateToken($id, $info)
    {
        $this->getTokenById($id)->addData($info);
        $this->getCurrentCustomer()->setDataChanges(true)->save();
    }

    /**
     * Delete token identified by id
     *
     * @param int $id id used in Magento, not Customer Token Id
     */
    public function deleteToken($id)
    {
        $this->updateToken($id, ['Active' => 0]);
    }

    /**
     * Set token identified by id as default token
     *
     * @param int $id id used in Magento, not Customer Token Id
     */
    public function setDefaultToken($id)
    {
        // Check if token is existed.
        $this->getTokenById($id);
        $this->getCurrentCustomer()->getSavedTokens()->setDefaultToken($id);
        $this->getCurrentCustomer()->setDataChanges(true)->save();
    }

    /**
     * Get default token id
     *
     * @return bool | int
     */
    public function getDefaultToken()
    {
        $customer = $this->getCurrentCustomer();
        if ($customer && $customer->getSavedTokens()) {
            return $customer->getSavedTokens()->getDefaultToken();
        }

        return false;
    }

    /**
     * Get active token list of current customer
     *
     * @return array
     */
    public function getActiveTokenList()
    {
        $customer = $this->getCurrentCustomer();
        if ($customer && $customer->getSavedTokens()) {
            $tokens = $customer->getSavedTokens()->getTokens();
            if (is_array($tokens)) {
                foreach ($tokens as $key => $token) {
                    /* @codingStandardsIgnoreLine @var Token $token */
                    if (!$token->getActive()) {
                        unset($tokens[$key]);
                    } else {
                        $token->unsetData('Token');
                    }
                }
                return $tokens;
            }
        }

        return [];
    }
}
