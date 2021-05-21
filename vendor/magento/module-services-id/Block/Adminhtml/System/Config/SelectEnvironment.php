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
use Magento\ServicesId\Model\MerchantRegistryProvider;
use Magento\ServicesId\Model\ServicesConfigInterface;

class SelectEnvironment extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_ServicesId::system/config/select-environment.phtml';

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
                'id' => 'services_connector_services_id_environment_id',
                'class' => 'select admin__control-select',
                'name' => 'groups[services_id][fields][environment_id][value]'
            ]
        );
        $select->setOptions($this->getOptionsArray());
        $select->setValue($this->servicesConfig->getEnvironmentId());
        $html = $select->toHtml();

        if (!$this->servicesConfig->isApiKeySet()
            || empty($this->merchantRegistryProvider->getMerchantRegistry())
            || $this->hasServiceError()
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
        $button = '';
        if ($this->servicesConfig->getEnvironmentId()) {
            $button .= $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'id' => 'rename_environment_button',
                    'label' => __('Rename this Environment'),
                    'disabled' => !$this->servicesConfig->isApiKeySet() || !$this->servicesConfig->getProjectId()
                ]
            )->toHtml();
        }

        return $button;
    }

    /**
     * Check if the action button should be rendered
     *
     *
     * @return bool
     */
    public function shouldRenderButton() : bool
    {
        $registryData = $this->merchantRegistryProvider->getMerchantRegistry();
        return !$this->hasServiceError() && !$this->isSaveRequired() && !empty($registryData);
    }

    /**
     * Options getter for environments by project
     *
     * @return array
     */
    public function getProjectEnvironmentOptionsArray() : array
    {
        $data = $this->merchantRegistryProvider->getMerchantRegistry();

        $projectEnvironments = [];
        if (!empty($data) && !isset($data['error'])) {
            $projects = [];
            foreach ($data as $key => $value) {
                $projects[] = $value['projectId'];
            }
            $projects = array_unique($projects);

            foreach ($data as $key => $value) {
                foreach ($projects as $project) {
                    if ($value['projectId'] == $project) {
                        $projectEnvironments[$project][$value['environmentId']] = [
                            'value' => $value['environmentId'],
                            'text' => $value['environmentName'] . ' [' . __('Type: ') . $value['environmentType'] . ']'
                        ];
                    }
                }
            }
        }
        return $projectEnvironments;
    }

    /**
     * Options getter
     *
     * @return array
     */
    private function getOptionsArray() : array
    {
        $data = $this->getProjectEnvironmentOptionsArray();

        $optionsArray = [];
        if (!empty($data)) {
            $setProjectId = $this->servicesConfig->getProjectId();
            reset($data);
            $projectId = array_key_exists($setProjectId, $data) ? $setProjectId : key($data);
            $optionsArray = [
                ['value' => null, 'label' => '-- Select Environment --']
            ];
            foreach ($data[$projectId] as $key => $value) {
                $optionsArray[] = [
                    'value' => $value['value'],
                    'label' => $value['text']
                ];
            }
        } else {
            $optionsArray = [
                ['value' => null, 'label' => 'No Environments Found']
            ];
        }
        return $optionsArray;
    }

    /**
     * Check if project data needs to be saved to the Magento DB
     *
     * @return bool
     */
    private function isSaveRequired() : bool
    {
        $merchantRegistry = $this->merchantRegistryProvider->getMerchantRegistry();
        return !$this->servicesConfig->getProjectId() && $merchantRegistry && !isset($merchantRegistry['error']);
    }

    /**
     * Check if there was an error retrieving project data
     *
     * @return bool
     */
    private function hasServiceError() : bool
    {
        return isset($this->merchantRegistryProvider->getMerchantRegistry()['error']);
    }
}
