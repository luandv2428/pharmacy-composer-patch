<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Model;

use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * @inheritdoc
 */
class ServicesConfig implements ServicesConfigInterface
{
    /**
     * Config path values for Services Id
     */
    const CONFIG_PATH_PROJECT_ID = 'services_connector/services_id/project_id';
    const CONFIG_PATH_PROJECT_NAME = 'services_connector/services_id/project_name';
    const CONFIG_PATH_ENVIRONMENT = 'services_connector/services_id/environment';
    const CONFIG_PATH_ENVIRONMENT_ID = 'services_connector/services_id/environment_id';
    const CONFIG_PATH_ENVIRONMENT_NAME = 'services_connector/services_id/environment_name';
    const CONFIG_PATH_REGISTRY_API_VERSION = 'services_connector/services_id/registry_api_version';

    /**
     * Config path values for Services Connector
     */
    const CONFIG_PATH_SERVICES_CONNECTOR_ENVIRONMENT = 'magento_saas/environment';
    const CONFIG_PATH_SERVICES_CONNECTOR_API_KEY = 'services_connector/services_connector_integration/{env}_api_key';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param ScopeConfigInterface $config
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $config,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList
    ) {
        $this->config = $config;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @inheritdoc
     */
    public function getProjectId() : ?string
    {
        return $this->config->getValue(self::CONFIG_PATH_PROJECT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getProjectName(): ?string
    {
        return $this->config->getValue(self::CONFIG_PATH_PROJECT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment() : ?string
    {
        return $this->config->getValue(self::CONFIG_PATH_ENVIRONMENT);
    }

    /**
     * @inheritDoc
     */
    public function getEnvironmentId() : ?string
    {
        return $this->config->getValue(self::CONFIG_PATH_ENVIRONMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getEnvironmentName(): ?string
    {
        return $this->config->getValue(self::CONFIG_PATH_ENVIRONMENT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function isApiKeySet() : bool
    {
        $apiKey = false;
        $environment = $this->config->getValue(self::CONFIG_PATH_SERVICES_CONNECTOR_ENVIRONMENT);
        if ($environment) {
            $apiKey = $this->config->getValue(str_replace(
                '{env}',
                $environment,
                self::CONFIG_PATH_SERVICES_CONNECTOR_API_KEY
            ));
        }
        return $apiKey ? true : false;
    }

    /**
     * @inheritDoc
     */
    public function getRegistryApiVersion() : string
    {
        return $this->config->getValue(self::CONFIG_PATH_REGISTRY_API_VERSION);
    }

    /**
     * @inheritDoc
     */
    public function setConfigValues(array $configs) : void
    {
        foreach ($configs as $key => $value) {
            $this->configWriter->save($key, $value);
        }
        $this->cacheTypeList->cleanType(CacheConfig::TYPE_IDENTIFIER);
    }
}
