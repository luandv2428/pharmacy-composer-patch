<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedCheckout\Controller\Sku;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\AdvancedCheckout\Controller\Sku as ControllerSku;

/**
 * The class collects products SKU from request
 */
class UploadFile extends ControllerSku implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Upload file Action
     *
     * @return void
     */
    public function execute()
    {
        /** @var $helper \Magento\AdvancedCheckout\Helper\Data */
        $helper = $this->_objectManager->get(\Magento\AdvancedCheckout\Helper\Data::class);
        $rows = $helper->isSkuFileUploaded($this->getRequest()) ? $helper->processSkuFileUploading() : [];

        $items = $this->getRequest()->getPost('items');
        if (!is_array($items)) {
            $items = [];
        }

        if (is_array($rows) && count($rows)) {
            foreach ($rows as $row) {
                $items[] = $row;
            }
        }

        $this->getRequest()->setParam('items', $items);
        $this->_forward('advancedAdd', 'cart');
    }
}
