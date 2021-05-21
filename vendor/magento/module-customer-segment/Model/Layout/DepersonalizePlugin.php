<?php
/**
 * Depersonalize customer session data
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerSegment\Model\Layout;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session;
use Magento\CustomerSegment\Helper\Data;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Depersonalize customer data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var array
     */
    protected $customerSegmentIds;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var Config
     */
    protected $cacheConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param Manager $moduleManager
     * @param HttpContext $httpContext
     * @param Config $cacheConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        Manager $moduleManager,
        HttpContext $httpContext,
        Config $cacheConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->httpContext = $httpContext;
        $this->cacheConfig = $cacheConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Resolve sensitive customer data.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function beforeGenerateXml(LayoutInterface $subject)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && !$this->request->isAjax()
            && $subject->isCacheable()
        ) {
            $this->customerSegmentIds = $this->customerSession->getCustomerSegmentIds();
        }
    }

    /**
     * Change sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     * @throws LocalizedException
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && !$this->request->isAjax()
            && $subject->isCacheable()
        ) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $value = $this->customerSegmentIds[$websiteId] ?? [];

            if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
                $this->httpContext->setValue(Data::CONTEXT_SEGMENT, $value, []);
            } else {
                $this->httpContext->setValue(Data::CONTEXT_SEGMENT, $value, $value);
            }

            $this->customerSession->setCustomerSegmentIds($this->customerSegmentIds);
        }
    }
}
