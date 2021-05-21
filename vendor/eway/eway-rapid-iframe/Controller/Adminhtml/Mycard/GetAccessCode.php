<?php
namespace Eway\IFrame\Controller\Adminhtml\Mycard;

use Eway\IFrame\Model\GetAccessCodeService;
use Magento\Backend\App\Action;
use Magento\Framework\DataObject;

class GetAccessCode extends Action
{
    /** @codingStandardsIgnoreLine @var GetAccessCodeService */
    protected $getAccessCodeService;

    public function __construct(
        Action\Context $context,
        GetAccessCodeService $getAccessCodeService
    ) {
    
        parent::__construct($context);
        $this->getAccessCodeService = $getAccessCodeService;
    }

    public function execute()
    {
        $request = $this->getRequest();
        // @codingStandardsIgnoreLine
        $data = new DataObject($request->getParams());

        return $this->getAccessCodeService->process($data);
    }
}
