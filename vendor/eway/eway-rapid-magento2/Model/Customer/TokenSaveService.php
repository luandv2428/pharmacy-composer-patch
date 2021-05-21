<?php
namespace Eway\EwayRapid\Model\Customer;

use Eway\EwayRapid\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;

class TokenSaveService
{
    const COMMAND_CREATE_TOKEN = 'create_token';
    const COMMAND_UPDATE_TOKEN = 'update_token';

    /** @var \Eway\EwayRapid\Model\Ui\ConfigProvider */
    protected $configProvider; // @codingStandardsIgnoreLine
    /**
     * @var \Eway\EwayRapid\Model\DummyPaymentInfoFactory
     */
    private $dummyPaymentInfoFactory;
    /**
     * @var \Eway\EwayRapid\Model\DummyPaymentDataObjectFactory
     */
    private $dummyPaymentDataObjectFactory; // @codingStandardsIgnoreLine

    public function __construct(
        \Eway\EwayRapid\Model\Ui\ConfigProvider $configProvider,
        \Eway\EwayRapid\Model\DummyPaymentInfoFactory $dummyPaymentInfoFactory,
        \Eway\EwayRapid\Model\DummyPaymentDataObjectFactory $dummyPaymentDataObjectFactory
    ) {
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
        $fields = [
            Config::SECURED_CARD_DATA,
            Config::ACCESS_CODE,
            Config::CARD_NUMBER,
            Config::CARD_NAME,
            Config::CARD_EXPIRY_MONTH,
            Config::CARD_EXPIRY_YEAR
        ];

        foreach ($fields as $field) {
            if ($value = $data->getData($field)) {
                $payment->setAdditionalInformation($field, $value);
            }
        }

        $paymentDataObject = $this->dummyPaymentDataObjectFactory->create(
            ['order' => $order, 'payment' => $payment]
        );

        $commandPool = $this->configProvider->getActiveMethodConfig()->getCommandPool();

        if ($tokenId) {
            $paymentDataObject->getPayment()->setAdditionalInformation(Config::TOKEN_ID, $tokenId);
            $commandPool->get(self::COMMAND_UPDATE_TOKEN)->execute([
                'payment' => $paymentDataObject,
            ]);
        } else {
            $commandPool->get(self::COMMAND_CREATE_TOKEN)->execute([
                'payment' => $paymentDataObject,
            ]);
        }
    }
}
