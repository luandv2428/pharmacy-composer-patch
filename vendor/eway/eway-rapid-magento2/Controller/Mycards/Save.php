<?php
namespace Eway\EwayRapid\Controller\Mycards;

use Eway\EwayRapid\Controller\AbstractMycards;
use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Customer\Token\ManagerInterface;
use Eway\EwayRapid\Model\Customer\TokenSaveService;
use Eway\EwayRapid\Model\Ui\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Gateway\ConfigInterface;

class Save extends AbstractMycards
{
    /** @codingStandardsIgnoreLine @var TokenSaveService */
    protected $tokenSaveService;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        ConfigInterface $config,
        PageFactory $pageFactory,
        ManagerInterface $tokenManager,
        TokenSaveService $tokenSaveService,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
        parent::__construct($context, $customerSession, $config, $pageFactory, $tokenManager);
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

        $this->messageManager->addSuccessMessage(__('Your Credit Card has been saved successfully.'));
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
