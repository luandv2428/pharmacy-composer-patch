<?php
namespace Eway\EwayRapid\Controller\Adminhtml\Mycards;

use Eway\EwayRapid\Controller\Adminhtml\AbstractMyCards;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\ConfigInterface;

class Save extends AbstractMyCards
{
    /** @codingStandardsIgnoreLine @var \Eway\EwayRapid\Model\Customer\TokenSaveService */
    protected $tokenSaveService;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    public function __construct(
        Action\Context $context,
        ConfigInterface $config,
        ManagerInterface $tokenManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Eway\EwayRapid\Model\Customer\TokenSaveService $tokenSaveService,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
        parent::__construct($context, $config, $tokenManager, $layoutFactory, $resultJsonFactory, $resultLayoutFactory);
        $this->tokenSaveService = $tokenSaveService;
        $this->directoryHelper = $directoryHelper;
    }

    // @codingStandardsIgnoreLine
    protected function _execute()
    {
        $request = $this->getRequest();
        // @codingStandardsIgnoreLine
        $data = new DataObject($request->getParams());

        $countryId = $data->getCountryId();
        $regionId = $data->getRegionId();

        if ($countryId && $regionId && $this->directoryHelper->isRegionRequired($countryId)) {
            $regionData = $this->directoryHelper->getRegionData();
            $data->setRegionCode($regionData[$countryId][$regionId]['code']);
        }

        $this->tokenSaveService->process($data);

        $this->messageManager->addSuccessMessage(__('Customer Credit Card has been saved successfully.'));
        return $this->success();
    }
}
