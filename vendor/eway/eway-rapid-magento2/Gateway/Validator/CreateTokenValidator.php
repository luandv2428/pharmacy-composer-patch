<?php
namespace Eway\EwayRapid\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;

class CreateTokenValidator extends AbstractResponseValidator
{

    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateCustomerTokenID($response)
            && $this->validateResponseCode($response)
            && $this->validateErrors($response);

        if (!$validationResult) {
            $errorMessages = [__('Error happened when saving card. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
