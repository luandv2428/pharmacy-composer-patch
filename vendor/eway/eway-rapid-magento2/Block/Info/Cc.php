<?php
namespace Eway\EwayRapid\Block\Info;

class Cc extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Payment\Gateway\ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->context = $context;
        $this->config = $config;
    }

    // @codingStandardsIgnoreLine
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        // Unset credit card type in case cannot detect it.
        $data = $transport->getData();

        if ($this->context->getAppState()->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $key = (string)__('Credit Card Type');
            if (isset($data[$key]) && $data[$key] == __('N/A')) {
                unset($data[$key]);
            }

            if ($this->getInfo()->getLastTransId()) {
                $data[(string)__('Last Transaction Id')] = $this->getInfo()->getLastTransId();
            }

            $beagleVerification = $this->getInfo()->getAdditionalInformation('beagle_verification');
            if ($beagleVerification) {
                if ($this->config->getValue('beagle_verify_email') && isset($beagleVerification['Email'])) {
                    $data[(string)__('Beagle Verification Email')] =
                        (string) $this->getBeagleVerificationText($beagleVerification['Email']);
                }

                if ($this->config->getValue('beagle_verify_phone') && isset($beagleVerification['Phone'])) {
                    $data[(string)__('Beagle Verification Phone')] =
                        (string) $this->getBeagleVerificationText($beagleVerification['Phone']);
                }
            }
        } else {
            // Hide all cc information in frontend
            $data = [];
        }

        return $transport->setData($data);
    }

    protected function getBeagleVerificationText($result) //@codingStandardsIgnoreLine
    {
        switch ($result) {
            case 0:
                return __('Not Verified');
            case 1:
                return __('Attempted');
            case 2:
                return __('Verified');
            case 3:
                return __('Failed');
            default:
                return __('Unknown');
        }
    }
}
