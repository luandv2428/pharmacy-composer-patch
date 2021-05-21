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

class RequestProject extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_ServicesId::system/config/request-project.phtml';

    /**
     * @var string
     */
    private const INITIALIZE_URL = 'services_id/index/initialize';

    /**
     * @var string
     */
    private const PROJECT_URL = 'services_id/index/project';

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
     * Return ajax url for the initialize route
     *
     * @return string
     */
    public function getInitializeUrl() : string
    {
        return $this->getUrl(self::INITIALIZE_URL);
    }

    /**
     * Return ajax url for the project route
     *
     * @return string
     */
    public function getProjectUrl() : string
    {
        return $this->getUrl(self::PROJECT_URL);
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml() : string
    {
        $projectId = $this->servicesConfig->getProjectId();
        $registryData = $this->merchantRegistryProvider->getMerchantRegistry();
        if ($projectId && $registryData) {
            $html = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'id' => 'update_project_button',
                    'label' => __('Save Project Name')
                ]
            )->toHtml();
        } else {
            $html = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'id' => 'request_project_button',
                    'label' => __('Create Project')
                ]
            )->toHtml();
        }

        $html .= $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
            [
                'id' => 'cancel_project_button',
                'label' => __('Cancel')
            ]
        )->toHtml();

        return $html;
    }
}
