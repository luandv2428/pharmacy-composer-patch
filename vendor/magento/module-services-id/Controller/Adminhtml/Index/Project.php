<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Controller\Adminhtml\Index;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ServicesId\Model\ServicesClientInterface;
use Magento\ServicesId\Model\ServicesConfig;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller responsible for communicating with the Magento SaaS Registry service
 */
class Project extends AbstractAction
{
    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var ServicesClientInterface
     */
    private $servicesClient;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ServicesConfigInterface $servicesConfig
     * @param ServicesClientInterface $servicesClient
     * @param Json $serializer
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ServicesConfigInterface $servicesConfig,
        ServicesClientInterface $servicesClient,
        Json $serializer,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->servicesConfig = $servicesConfig;
        $this->servicesClient = $servicesClient;
        $this->serializer = $serializer;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute middleware call
     *
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        $jsonResult = $this->resultJsonFactory->create();

        $apiVersion = $this->servicesConfig->getRegistryApiVersion();
        $method = $this->getRequest()->getParam('method', 'GET');
        $uri = $this->getRequest()->getParam('uri', '');
        $projectName = $this->getRequest()->getParam('projectName');

        $payload = ['projectName' => $projectName];
        $url = $this->servicesClient->getUrl($apiVersion, $uri);
        $result = $this->servicesClient->request($method, $url, $this->serializer->serialize($payload));

        if (!empty($result['results'])) {
            try {
                $configs = [
                    ServicesConfig::CONFIG_PATH_PROJECT_NAME => $result['results'][0]['projectName']
                ];
                $this->servicesConfig->setConfigValues($configs);
            } catch (\Exception $ex) {
                $message = __('An error occurred saving configuration. Please click Save Config.');
                $this->getMessageManager()->addErrorMessage($message);
                $this->logger->error($ex->getMessage());
            }
        }

        return $jsonResult->setData($result);
    }
}
