<?php
namespace Eway\DirectConnection\Gateway\Request;

use Eway\EwayRapid\Model\Config;

class CreateTokenCardDetailsDataBuilder extends CardDetailsDataBuilder
{
    public function build(array $buildSubject)
    {
        $result = parent::build($buildSubject);
        return $result[Config::CUSTOMER];
    }
}
