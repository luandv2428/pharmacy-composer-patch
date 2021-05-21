<?php
namespace Eway\IFrame\Controller\Redirect;

use Eway\EwayRapid\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(
        Context $context,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($context);
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
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
            $cartId = $this->getRequest()->getParam('cart_id');
            $accessCode = $this->getRequest()->getParam(Config::ACCESS_CODE);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->getActive($cartId);
            if ($quote->getPayment()->getAdditionalInformation(Config::ACCESS_CODE) != $accessCode) {
                throw new LocalizedException(__('Your session is expired, please try again.'));
            }

            if (!$quote->getCustomerId()) {
                $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            }
            $this->cartManagement->placeOrder($cartId);

            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
