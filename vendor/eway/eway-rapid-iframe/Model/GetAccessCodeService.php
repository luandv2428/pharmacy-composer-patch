<?php
namespace Eway\IFrame\Model;

use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Ui\ConfigProvider;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class GetAccessCodeService
{
    const COMMAND_GET_ACCESS_CODE_CREATE = 'get_access_code_create';
    const COMMAND_GET_ACCESS_CODE_UPDATE = 'get_access_code_update';

    /** @codingStandardsIgnoreLine @var JsonFactory */
    protected $resultJsonFactory;

    /** @codingStandardsIgnoreLine @var ConfigProvider */
    protected $configProvider;
    /**
     * @var Eway\EwayRapid\Model\DummyPaymentInfoFactory
     */
    private $dummyPaymentInfoFactory;
    /**
     * @var Eway\EwayRapid\Model\DummyPaymentDataObjectFactory
     */
    private $dummyPaymentDataObjectFactory;

    public function __construct(
        ConfigProvider $configProvider,
        JsonFactory $resultJsonFactory,
        \Eway\EwayRapid\Model\DummyPaymentInfoFactory $dummyPaymentInfoFactory,
        \Eway\EwayRapid\Model\DummyPaymentDataObjectFactory $dummyPaymentDataObjectFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configProvider = $configProvider;
        $this->dummyPaymentInfoFactory = $dummyPaymentInfoFactory;
        $this->dummyPaymentDataObjectFactory = $dummyPaymentDataObjectFactory;
    }

    public function process(DataObject $data)
    {
        $tokenId = $data->getData('token_id');
        // @codingStandardsIgnoreLine
        $order = new DataObject();
        $billingAddress = $data;
        $order->setBillingAddress($billingAddress);

        $payment = $this->dummyPaymentInfoFactory->create();

        $paymentDataObject = $this->dummyPaymentDataObjectFactory->create(
            ['order' => $order, 'payment' => $payment]
        );

        $commandPool = $this->configProvider->getActiveMethodConfig()->getCommandPool();

        try {
            if ($tokenId) {
                $paymentDataObject->getPayment()->setAdditionalInformation(Config::TOKEN_ID, $tokenId);
                $result = $commandPool->get(self::COMMAND_GET_ACCESS_CODE_UPDATE)->execute([
                    'payment' => $paymentDataObject,
                ]);
            } else {
                $paymentDataObject->getPayment()
                    ->setAdditionalInformation(Config::TOKEN_ACTION, Config::TOKEN_ACTION_NEW);
                $result = $commandPool->get(self::COMMAND_GET_ACCESS_CODE_CREATE)->execute([
                    'payment' => $paymentDataObject,
                ]);
            }

            $result = $result->get();

            if (!isset($result[Config::ACCESS_CODE])) {
                throw new LocalizedException(__('Error happened when getting access code, please try again later'));
            }

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
