<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSyncAdmin\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Index
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * Config paths
     */
    const CONTENT_SECURITY_POLICY_CONFIG_PATH = 'catalog_sync_admin/content_security_policy';

    const MENU_ID = 'Magento_CatalogSyncAdmin::catalog_sync_admin';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $config,
        StoreManagerInterface  $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Load the page defined in view/adminhtml/layout/catalog_sync_admin_index_index.xml
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Catalog Sync'));
        $resultPage->setHeader(
            'Content-Security-Policy',
            "default-src 'self' data: 'unsafe-inline' 'unsafe-eval' "
            . $this->config->getValue(self::CONTENT_SECURITY_POLICY_CONFIG_PATH)
        );
        $this->setStoreView();
        return $resultPage;
    }

    /**
     * Set store view to store switcher
     *
     * @throws NoSuchEntityException
     */
    private function setStoreView(): void
    {
        $params = $this->getRequest()->getParams();
        $storeId = isset($params['store']) ? $params['store'] : $this->getRequest()->getParam('store');
        $store = $this->storeManager->getStore($storeId);
        $params['store'] = $store->getId();
        $this->getRequest()->setParams($params);
    }
}
