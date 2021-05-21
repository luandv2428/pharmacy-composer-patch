<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Message\ManagerInterface;
use Magento\ServicesId\Model\MerchantRegistryProvider;
use Magento\ServicesId\Model\ServicesConfigInterface;

class SelectProject extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_ServicesId::system/config/select-project.phtml';

    /**
     * @var string
     */
    private const ERROR_LINK_URL = 'https://devdocs.magento.com/guides/v2.3/config-guide/saas/environment.html';

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var MerchantRegistryProvider
     */
    private $merchantRegistryProvider;

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param MerchantRegistryProvider $merchantRegistryProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        MerchantRegistryProvider $merchantRegistryProvider,
        array $data = []
    ) {
        $this->servicesConfig = $servicesConfig;
        $this->merchantRegistryProvider = $merchantRegistryProvider;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) : string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) : string
    {
        return $this->_toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSelectHtml() : string
    {
        $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')->setData(
            [
                'id' => 'services_connector_services_id_project_id',
                'class' => 'select admin__control-select',
                'name' => 'groups[services_id][fields][project_id][value]'
            ]
        );
        $select->setOptions($this->getOptionsArray());
        $select->setValue($this->servicesConfig->getProjectId());
        $html = $select->toHtml();

        if (!$this->servicesConfig->isApiKeySet()
            || empty($this->merchantRegistryProvider->getMerchantRegistry())
        ) {
            $html = str_replace('<select ', '<select disabled ', $html);
        }

        return $html;
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml() : string
    {
        $html = '';
        $projectId = $this->servicesConfig->getProjectId();
        $registryData = $this->merchantRegistryProvider->getMerchantRegistry();
        if (!$projectId || !$registryData) {
            $label = __('Create Project');
            $html = '<br />';
        } else {
            $label = __('Rename this Project');
        }

        $html .= $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
            [
                'id' => 'create_project_button',
                'label' => $label,
                'disabled' => !$this->servicesConfig->isApiKeySet()
            ]
        )->toHtml();

        return $html;
    }

    /**
     * Get error messages if present
     *
     * @return string|null
     */
    public function getErrorMessage() : ?string
    {
        $message = null;
        $merchantRegistryData = $this->merchantRegistryProvider->getMerchantRegistry();
        if (isset($merchantRegistryData['error'])) {
            $message = $merchantRegistryData['message'];
        } elseif ($this->servicesConfig->getProjectId() && !$merchantRegistryData) {
            $errorText = __('Your SaaS Project ID or Environment ID is invalid. Check the values, including in the database.');
            $linkText = __('Learn More');
            $message = $errorText . ' <a href="' . self::ERROR_LINK_URL . '" target="_blank" title="'
                . $errorText . '">' . $linkText . '</a>';
        }
        return (string) $message;
    }

    /**
     * Check if project data needs to be saved to the Magento DB
     *
     * @return bool
     */
    public function isSaveRequired() : bool
    {
        $merchantRegistry = $this->merchantRegistryProvider->getMerchantRegistry();
        return !$this->servicesConfig->getProjectId() && $merchantRegistry && !isset($merchantRegistry['error']);
    }

    /**
     * Get options array for select
     *
     * @return array
     */
    private function getOptionsArray() : array
    {
        $data = $this->merchantRegistryProvider->getMerchantRegistry();
        $optionsArray = [];
        if (!empty($data) && !isset($data['error'])) {
            foreach ($data as $key => $value) {
                if (!in_array($value['projectId'], array_column($optionsArray, 'value'))) {
                    $optionsArray[] = [
                        'value' => $value['projectId'],
                        'label' => $value['projectName']
                    ];
                }
            }
        } else {
            $optionsArray = [
                ['value' => null, 'label' => 'No Projects Found']
            ];
        }
        return $optionsArray;
    }
}
