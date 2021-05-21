<?php
namespace Eway\IFrame\Gateway\Request;

use Eway\EwayRapid\Model\Config;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class MyCardCallbackUrlBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $paymentModel = $payment->getPayment();

        $params = [];
        $tokenId = $paymentModel->getAdditionalInformation(Config::TOKEN_ID);
        if ($tokenId) {
            $params['token_id'] = $tokenId;
        }
        return [
            Config::REDIRECT_URL => $this->urlBuilder->getUrl('ewayrapid/mycard/success', $params),
            Config::CANCEL_URL   => $this->urlBuilder->getUrl('ewayrapid/mycard/cancel'),
        ];
    }
}
