<?php
namespace Eway\DirectConnection\Gateway\Validator;

use Eway\EwayRapid\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;
use \Eway\EwayRapid\Model\Config;

class ResponseValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionType($response)
            && $this->validateTransactionStatus($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseMessage($response)
            && $this->validateAuthorisationCode($response)
            && $this->validateCardDetails($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined, please, try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * @param array $response
     * @return bool
     */
    private function validateCardDetails(array $response)
    {
        return !empty($response[Config::CUSTOMER][Config::CARD_DETAILS]);
    }
}
