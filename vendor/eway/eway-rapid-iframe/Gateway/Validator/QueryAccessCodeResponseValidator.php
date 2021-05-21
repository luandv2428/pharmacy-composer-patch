<?php
namespace Eway\IFrame\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;

class QueryAccessCodeResponseValidator extends \Eway\EwayRapid\Gateway\Validator\AbstractResponseValidator
{
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined, please, try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
