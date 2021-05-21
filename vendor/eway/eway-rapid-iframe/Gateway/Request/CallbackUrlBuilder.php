<?php
namespace Eway\IFrame\Gateway\Request;

use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Eway\EwayRapid\Model\Config;

class CallbackUrlBuilder implements BuilderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(UrlInterface $urlBuilder, Session $checkoutSession)
    {
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
    }

    // @codingStandardsIgnoreLine
    public function build(array $buildSubject)
    {
        $params = [];
        if ($this->checkoutSession->hasQuote()) {
            $params['cart_id'] = $this->checkoutSession->getQuoteId();
        }
        return [
            Config::REDIRECT_URL => $this->urlBuilder->getUrl('ewayrapid/redirect/success', $params),
            Config::CANCEL_URL   => $this->urlBuilder->getUrl('ewayrapid/redirect/cancel'),
        ];
    }
}
