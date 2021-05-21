<?php
/**
 * Copyright © Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductRecommendationsAdmin\Block\Adminhtml;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends Template
{
    /**
     * Config Paths
     * @var string
     */
    private const FRONTEND_URL_PATH = 'product_recommendations/frontend_url';
    const CONFIG_PATH_API_KEY = 'services_connector/services_connector_integration/production_api_key';
    const CONFIG_PATH_ALTERNATE_ENVIRONMENT_ENABLED = 'services_connector/product_recommendations/alternate_environment_enabled';
    const CONFIG_PATH_ALTERNATE_ENVIRONMENT_ID = 'services_connector/product_recommendations/alternate_environment_id';

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig
    ) {
        $this->servicesConfig = $servicesConfig;
        parent::__construct($context);
    }

    /**
     * Returns config for frontend url
     *
     * @return string
     */
    public function getFrontendUrl(): string
    {
        return (string) $this->_scopeConfig->getValue(
            self::FRONTEND_URL_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get store view code from store switcher
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreViewCode(): string
    {
        $storeId = $this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId)->getCode();
    }

    /**
     * Get website code
     *
     * @return string
     * @throws LocalizedException
     */
    public function getWebsiteCode(): string
    {
        $websiteId = $this->getRequest()->getParam('website');
        return $this->_storeManager->getWebsite($websiteId)->getCode();
    }

    /**
     * Get Environment Id from Services Id configuration
     *
     * @return string|null
     */
    public function getEnvironmentId(): ?string
    {
        return $this->servicesConfig->getEnvironmentId();
    }

    /**
     * Get Environment Name from Services Id configuration
     *
     * @return string|null
     */
    public function getEnvironmentName(): ?string
    {
        return $this->servicesConfig->getEnvironmentName();
    }

    /**
     * Get Environment Type from Services Id configuration
     *
     * @return string|null
     */
    public function getEnvironmentType(): ?string
    {
        return $this->servicesConfig->getEnvironment();
    }

    /**
     * Get alternate Environment Id to fetch recommendations from configuration
     *
     * @return string|null
     */
    public function getAlternateEnvironmentId(): ?string
    {
        return $this->_scopeConfig->getValue(self::CONFIG_PATH_ALTERNATE_ENVIRONMENT_ID);
    }

    /**
     * Check if alternate recommendations environment is being used for recommendations
     *
     * @return bool
     */
    public function isAlternateEnvironmentEnabled(): bool
    {
        return (bool) $this->_scopeConfig->getValue(self::CONFIG_PATH_ALTERNATE_ENVIRONMENT_ENABLED);
    }

    /**
     * Get API Key from Services Connector configuration
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->_scopeConfig->getValue(self::CONFIG_PATH_API_KEY);
    }

    /**
     * Get store base currency code
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
}
