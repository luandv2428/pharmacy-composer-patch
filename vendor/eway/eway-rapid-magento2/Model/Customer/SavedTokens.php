<?php
namespace Eway\EwayRapid\Model\Customer;

use Eway\EwayRapid\Model\Customer\CustomerInfoFactory;
use Eway\EwayRapid\Model\Customer\TokenFactory;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class \Eway\EwayRapid\Model\Customer\SavedTokens
 *
 *
 * @method int getLastId()
 * @method $this setLastId(int $value)
 * @method int getDefaultToken()
 * @method $this setDefaultToken(int $value)
 * @method array getTokens()
 * @method $this setTokens(array $value)
 */
class SavedTokens extends JsonSerializableAbstract
{
    /** @var TokenFactory */
    protected $tokenFactory; // @codingStandardsIgnoreLine

    /** @var CustomerInfoFactory */
    protected $customerInfoFactory; // @codingStandardsIgnoreLine

    public function __construct(
        TokenFactory $tokenFactory,
        CustomerInfoFactory $customerInfoFactory,
        array $data = []
    ) {
    
        parent::__construct($data);
        $this->tokenFactory = $tokenFactory;
        $this->customerInfoFactory = $customerInfoFactory;
    }

    /**
     * @param $json string|array
     * @return $this
     */
    public function decodeJSON($json)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        /*
        $json = array(
                'LastId' 		=> <last token id>
                'DefaultToken' => <default token id>
                'Tokens'		=> array(
                    <token id> => array(
                        'Token' 	=> <eWAY customer token>,
                        'Card'		=> <masked card number>,
                        'Type'		=> <credit card type, e.g: VI, MA>
                        'Owner'		=> <owner>,
                        'ExpMonth'  => <expired month>,
                        'ExpYear' 	=> <expired year>,
                        'Active' 	=> 0 | 1,
                        'Address'	=> array(
                            'FirstName' => <first name>
                            ...
                        )
                    ),
                )
            )
         */

        $this->addData($json);
        $tokens = $this->getTokens();
        if (is_array($tokens)) {
            foreach ($tokens as $id => $token) {
                $tokenModel = $this->tokenFactory->create()->addData($token);
                /* @codingStandardsIgnoreLine @var Token $tokenModel */
                if ($address = $tokenModel->getAddress()) {
                    $tokenModel->setAddress($this->customerInfoFactory->create()->addData($address));
                }
                $tokens[$id] = $tokenModel;
            }

            $this->setTokens($tokens);
        }

        return $this;
    }

    /**
     * @param $id
     * @return \Eway\EwayRapid\Model\Customer\Token
     * @throws NotFoundException
     */
    public function getTokenById($id)
    {
        if (($tokens = $this->getTokens()) && isset($tokens[$id]) && $tokens[$id] instanceof Token) {
            return $tokens[$id]->setId($id);
        } else {
            throw new NotFoundException(__('Customer token does not exist.'));
        }
    }

    public function addToken($info)
    {
        $lastId = $this->getLastId() ?: 0;
        $tokens = $this->getTokens() ?: [];

        $this->setLastId($lastId + 1);
        $tokens[$this->getLastId()] = $this->tokenFactory->create()->addData($info)->setActive(1);
        $this->setTokens($tokens);

        return $this->getLastId();
    }
}
