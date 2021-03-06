<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\DataServices\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as PersonalizationSession;
use Magento\DataServices\Model\ProductContextInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * WishListDelete class
 *
 * @api
 */
class WishListDelete implements ObserverInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PersonalizationSession
     */
    private $personalizationSession;

    /**
     * @var ProductContextInterface
     */
    private $productContext;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PersonalizationSession $personalizationSession
     * @param ProductContextInterface $productContext
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PersonalizationSession $personalizationSession,
        ProductContextInterface $productContext
    ) {
        $this->productRepository = $productRepository;
        $this->personalizationSession = $personalizationSession;
        $this->productContext = $productContext;
    }

    /**
     * Get product data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store_id = $observer->getData('item')->getOrigData('store_id');
        $product_id = $observer->getData('item')->getOrigData('product_id');
        $item = $this->productRepository->getById($product_id, false, $store_id, false);
        $this->personalizationSession->setUserAction("remove-from-wishlist");
        $this->personalizationSession->setProductContext($this->productContext->getContextData($item));
    }
}
