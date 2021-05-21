<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Model;

/**
 * Interface for SaaS Services configuration values
 *
 * @api
 */
interface ServicesConfigInterface
{
    /**
     * Get Project ID for SaaS Services
     *
     * @return string|null
     */
    public function getProjectId() : ?string;

    /**
     * Get Project Name for SaaS Services
     *
     * @return string|null
     */
    public function getProjectName() : ?string;

    /**
     * Get Environment for SaaS Services
     *
     * @return string|null
     */
    public function getEnvironment() : ?string;

    /**
     * Get Environment ID for SaaS Services
     *
     * @return string|null
     */
    public function getEnvironmentId() : ?string;

    /**
     * Get Environment Name for SaaS Services
     *
     * @return string|null
     */
    public function getEnvironmentName() : ?string;

    /**
     * Check is API Key is set in Services Connector
     *
     * @return bool
     */
    public function isApiKeySet() : bool;

    /**
     * Get Registry API version to use
     *
     * @return string
     */
    public function getRegistryApiVersion() : string;

    /**
     * Set values to store configuration
     *
     * @param array $configs
     * @return void
     */
    public function setConfigValues(array $configs) : void;
}
