<?php
namespace Eway\EwayRapid\Gateway\Validator;

use Eway\EwayRapid\Gateway\Request\BaseRequestDataBuilder;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use \Eway\EwayRapid\Model\Config;

/**
 * Class AbstractResponseValidator
 */
abstract class AbstractResponseValidator extends AbstractValidator
{
    protected function validateCustomerTokenID(array $response) // @codingStandardsIgnoreLine
    {
        return !empty($response[Config::CUSTOMER][Config::TOKEN_CUSTOMER_ID]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateErrors(array $response) // @codingStandardsIgnoreLine
    {
        return empty($response[Config::ERRORS]);
    }

    /**
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount) // @codingStandardsIgnoreLine
    {
        return isset($response[Config::PAYMENT][Config::TOTAL_AMOUNT])
        && (string)($response[Config::PAYMENT][Config::TOTAL_AMOUNT] / 100) === (string)$amount;
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionType(array $response) // @codingStandardsIgnoreLine
    {
        return isset($response[Config::TRANSACTION_TYPE])
        && in_array($response[Config::TRANSACTION_TYPE], [Config::PURCHASE, Config::MOTO, Config::RECURRING]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionStatus(array $response) // @codingStandardsIgnoreLine
    {
        return isset($response[Config::TRANSACTION_STATUS])
        && $response[Config::TRANSACTION_STATUS] === true;
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionId(array $response) // @codingStandardsIgnoreLine
    {
        return !empty($response[Config::TRANSACTION_ID]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response) // @codingStandardsIgnoreLine
    {
        return isset($response[Config::RESPONSE_CODE])
        && (
               $response[Config::RESPONSE_CODE] === Config::RESPONSE_CODE_ACCEPT
               || $response[Config::RESPONSE_CODE] === Config::RESPONSE_CODE_ACCEPT_2
       );
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseMessage(array $response) // @codingStandardsIgnoreLine
    {
        return !empty($response[Config::RESPONSE_MESSAGE]);
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateAuthorisationCode(array $response) // @codingStandardsIgnoreLine
    {
        return !empty($response[Config::AUTHORISATION_CODE]);
    }
}
