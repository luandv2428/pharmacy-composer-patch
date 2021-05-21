<?php
namespace Eway\IFrame\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use \Eway\EwayRapid\Model\Config;

class TransactionQueryResponseValidator extends \Eway\EwayRapid\Gateway\Validator\AbstractResponseValidator
{
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTransactionStatus($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseMessage($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined, please, try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    protected function validateTotalAmount(array $response, $amount) // @codingStandardsIgnoreLine
    {
        return isset($response[Config::TOTAL_AMOUNT])
        && (string)($response[Config::TOTAL_AMOUNT] / 100) === (string)$amount;
    }
}
