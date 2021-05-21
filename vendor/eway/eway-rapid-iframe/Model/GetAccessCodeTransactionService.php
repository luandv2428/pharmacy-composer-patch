<?php
namespace Eway\IFrame\Model;

use Eway\EwayRapid\Model\Config;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

class GetAccessCodeTransactionService
{
    const COMMAND_GET_ACCESS_CODE  = 'get_access_code';

    /** @codingStandardsIgnoreLine @var CommandPoolInterface */
    protected $commandPool;

    /** @codingStandardsIgnoreLine @var PaymentDataObjectFactory */
    protected $paymentDataObjectFactory;

    /** @codingStandardsIgnoreLine @var JsonFactory */
    protected $resultJsonFactory;

    /** @codingStandardsIgnoreLine @var ConfigInterface */
    protected $config;

    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        JsonFactory $resultJsonFactory,
        ConfigInterface $config
    ) {
    
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
    }

    public function process(\Magento\Quote\Model\Quote $quote, \Magento\Framework\App\RequestInterface $request)
    {
        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId()->save();
        }
        $payment = $quote->getPayment();

        if ($this->config->getValue('token_enabled')) {
            // Reset token information
            $payment->setAdditionalInformation(Config::TOKEN_ACTION, null);
            $payment->setAdditionalInformation(Config::TOKEN_ID, null);

            if ($action = $request->getParam(Config::TOKEN_ACTION)) {
                $payment->setAdditionalInformation(Config::TOKEN_ACTION, $action);

                if ($tokenID = $request->getParam(Config::TOKEN_ID)) {
                    $payment->setAdditionalInformation(Config::TOKEN_ID, $tokenID);
                }
            }

            $payment->save();
        }

        $paymentDataObject = $this->paymentDataObjectFactory->create($payment);

        try {
            $result = $this->commandPool->get(self::COMMAND_GET_ACCESS_CODE)->execute([
                'payment' => $paymentDataObject,
                'amount' => $quote->getGrandTotal()
            ]);

            $result = $result->get();

            if (!isset($result[Config::ACCESS_CODE])) {
                throw new LocalizedException(__('Error happened when getting access code, please try again later'));
            }

            $payment->setAdditionalInformation(Config::ACCESS_CODE, $result[Config::ACCESS_CODE]);
            $payment->save();

            $jsonData = [
                'access_code' => $result[Config::ACCESS_CODE]
            ];
            if (isset($result[Config::SHARED_PAYMENT_URL])) {
                $jsonData['shared_payment_url'] = $result[Config::SHARED_PAYMENT_URL];
            }
            if (isset($result[Config::FORM_ACTION_URL])) {
                $jsonData['form_action_url'] = $result[Config::FORM_ACTION_URL];
            }

            return $this->resultJsonFactory->create()->setData($jsonData);
        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setHttpResponseCode(400)->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}
