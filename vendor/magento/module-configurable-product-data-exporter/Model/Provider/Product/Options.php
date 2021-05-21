<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductDataExporter\Model\Provider\Product;

use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogDataExporter\Model\Provider\Product\OptionProviderInterface;
use Magento\ConfigurableProductDataExporter\Model\Query\ProductOptionQuery;
use Magento\ConfigurableProductDataExporter\Model\Query\ProductOptionValueQuery;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;

/**
 * Configurable product options data provider
 */
class Options implements OptionProviderInterface
{
    /**
     * Configurable Product Super Option Type
     */
    public const CONFIGURABLE_RELATION_TYPE = 'super';

    /**
     * Batch sizing for performing queries
     *
     * @var int
     */
    private $batchSize;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductOptionQuery
     */
    private $productOptionQuery;

    /**
     * @var ProductOptionValueQuery
     */
    private $productOptionValueQuery;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductOptionQuery $productOptionQuery
     * @param ProductOptionValueQuery $productOptionValueQuery
     * @param QueryGenerator $queryGenerator
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductOptionQuery $productOptionQuery,
        ProductOptionValueQuery $productOptionValueQuery,
        QueryGenerator $queryGenerator,
        LoggerInterface $logger,
        int $batchSize
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productOptionQuery = $productOptionQuery;
        $this->productOptionValueQuery = $productOptionValueQuery;
        $this->logger = $logger;
        $this->queryGenerator = $queryGenerator;
        $this->batchSize = $batchSize;
    }

    /**
     * Retrieve query data in batches
     *
     * @param Select $select
     * @param string $rangeField
     * @return \Generator
     * @throws UnableRetrieveData
     */
    private function getBatchedQueryData(Select $select, string $rangeField): \Generator
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $iterator = $this->queryGenerator->generate(
                $rangeField,
                $select,
                $this->batchSize
            );
            foreach ($iterator as $batchSelect) {
                yield $connection->fetchAll($batchSelect);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve configurable option data');
        }
    }

    /**
     * Get option values
     *
     * @param array $arguments
     * @return array
     * @throws UnableRetrieveData
     */
    private function getOptionValuesData(array $arguments): array
    {
        $optionValues = [];
        $select = $this->productOptionValueQuery->getQuery($arguments);
        foreach ($this->getBatchedQueryData($select, 'attribute_id') as $batchData) {
            foreach ($batchData as $row) {
                $optionValues[$row['attribute_id']][$row['storeViewCode']][$row['optionId']] = [
                    'id' => $row['optionId'],
                    'default_label' => $row['default_label'],
                    'store_label' => $row['store_label'],
                ];
            }
        }
        return $optionValues;
    }

    /**
     * Format options row in appropriate format for feed data storage
     *
     * @param array $row
     * @return array
     */
    private function formatOptionsRow($row): array
    {
        return [
            'productId' => $row['productId'],
            'storeViewCode' => $row['storeViewCode'],
            'options' => [
                'id' => $row['super_attribute_id'],
                'attribute_id' => $row['attribute_id'],
                'attribute_code' => $row['attribute_code'],
                'use_default' => (bool)$row['use_default'],
                'type' => self::CONFIGURABLE_RELATION_TYPE,
                'title' => $row['title'],
                'sort_order' => $row['position']
            ]
        ];
    }

    /**
     * Generate option key by concatenating productId, storeViewCode and attributeId
     *
     * @param array $row
     * @return string
     */
    private function getOptionKey($row): string
    {
        return $row['productId'] . $row['storeViewCode'] . $row['attribute_id'];
    }

    /**
     * @inheritDoc
     */
    public function get(array $values): array
    {
        $queryArguments = [];
        foreach ($values as $value) {
            $queryArguments['productId'][$value['productId']] = $value['productId'];
            $queryArguments['storeViewCode'][$value['storeViewCode']] = $value['storeViewCode'];
        }

        try {
            $options = [];
            $setOptionValues = [];

            $optionValuesData = $this->getOptionValuesData($queryArguments);
            $select = $this->productOptionQuery->getQuery($queryArguments);
            foreach ($this->getBatchedQueryData($select, 'entity_id') as $batchData) {
                foreach ($batchData as $row) {
                    $key = $this->getOptionKey($row);
                    $options[$key] = $options[$key] ?? $this->formatOptionsRow($row);

                    if (!isset($setOptionValues[$key . $row['value']])) {
                        $setOptionValues[$key . $row['value']] = true;
                        $options[$key]['options']['values'][] =
                            $optionValuesData[$row['attribute_id']][$row['storeViewCode']][$row['value']];
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve configurable product options data');
        }
        return $options;
    }
}
