<?php
namespace Eway\IFrame\Controller\Mycard;

use Eway\IFrame\Model\GetAccessCodeService;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\DataObject;

class GetAccessCode extends Action
{
    /** @codingStandardsIgnoreLine @var GetAccessCodeService */
    protected $getAccessCodeService;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    public function __construct(
        Context $context,
        GetAccessCodeService $getAccessCodeService,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
    
        parent::__construct($context);
        $this->getAccessCodeService = $getAccessCodeService;
        $this->directoryHelper = $directoryHelper;
    }

    public function execute()
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

        return $this->getAccessCodeService->process($data);
    }
}
