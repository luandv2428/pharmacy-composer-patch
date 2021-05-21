<?php
namespace Eway\IFrame\Controller\Mycard;

use Eway\EwayRapid\Model\Config;
use Eway\EwayRapid\Model\Customer\TokenSaveService;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var TokenSaveService
     */
    private $tokenSaveService;

    public function __construct(Context $context, TokenSaveService $tokenSaveService)
    {
        parent::__construct($context);
        $this->tokenSaveService = $tokenSaveService;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $accessCode = $this->getRequest()->getParam(Config::ACCESS_CODE);
            if (!$accessCode) {
                throw new LocalizedException(__('There is problem with the request, please try again.'));
            }
            // @codingStandardsIgnoreLine
            $data = new DataObject([Config::ACCESS_CODE => $accessCode]);

            $tokenId = $this->getRequest()->getParam('token_id');
            if ($tokenId) {
                $data->setData('token_id', $tokenId);
            }
            $this->tokenSaveService->process($data);

            $this->messageManager->addSuccessMessage(__('Your Credit Card has been saved successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/mycards/index/');
    }
}
