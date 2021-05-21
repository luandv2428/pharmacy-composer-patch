<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\BundleProductDataExporter\Model\Provider\Product;

use Magento\BundleProductDataExporter\Model\Query\BundleProductOptionsQuery;
use Magento\BundleProductDataExporter\Model\Query\BundleProductOptionValuesQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogDataExporter\Model\Provider\Product\OptionProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class which provides bundle product options and option values
 */
class BundleProductOptions implements OptionProviderInterface
{
    /**
     * Bundle products relation type
     */
    private const BUNDLE_RELATION_TYPE = 'super';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var BundleProductOptionsQuery
     */
    private $bundleProductOptionsQuery;

    /**
     * @var BundleProductOptionValuesQuery
     */
    private $bundleProductOptionValuesQuery;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $priceTypes = [
        'FIXED',
        'PERCENT',
        'DYNAMIC',
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param BundleProductOptionsQuery $bundleProductOptionsQuery
     * @param BundleProductOptionValuesQuery $bundleProductOptionValuesQuery
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        BundleProductOptionsQuery $bundleProductOptionsQuery,
        BundleProductOptionValuesQuery $bundleProductOptionValuesQuery,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->bundleProductOptionsQuery = $bundleProductOptionsQuery;
        $this->bundleProductOptionValuesQuery = $bundleProductOptionValuesQuery;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function get(array $values) : array
    {
        $output = [];
        $queryArguments = [];
        $productIds = [];

        foreach ($values as $value) {
            $productIds[$value['productId']] = $value['productId'];
            $queryArguments[$value['storeViewCode']][$value['productId']] = $value['productId'];
        }

        try {
            foreach ($queryArguments as $storeViewCode => $productIds) {
                $optionValues = $this->getOptionValues($productIds, $storeViewCode);
                $cursor = $this->resourceConnection->getConnection()->query(
                    $this->bundleProductOptionsQuery->getQuery($productIds, $storeViewCode)
                );

                while ($row = $cursor->fetch()) {
                    $output[] = $this->formatBundleOptionsRow($row, $optionValues);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve bundle product options data');
        }

        return $output;
    }

    /**
     * Get bundle products option values
     *
     * @param int[] $productIds
     * @param string $storeViewCode
     *
     * @return array
     */
    private function getOptionValues(array $productIds, string $storeViewCode) : array
    {
        $output = [];

        $cursor = $this->resourceConnection->getConnection()->query(
            $this->bundleProductOptionValuesQuery->getQuery($productIds, $storeViewCode)
        );

        while ($row = $cursor->fetch()) {
            $output[$row['parent_id']][$row['option_id']][] = $this->formatBundleValuesRow($row);
        }

        return $output;
    }

    /**
     * Format bundle item options row
     *
     * @param array $row
     * @param array $optionValues
     *
     * @return array
     */
    private function formatBundleOptionsRow(array $row, array $optionValues) : array
    {
        return [
            'productId' => $row['product_id'],
            'storeViewCode' => $row['store_view_code'],
            'options' => [
                'type' => self::BUNDLE_RELATION_TYPE,
                'id' => $row['option_id'],
                'render_type' => $row['render_type'],
                'is_required' => $row['is_required'],
                'title' => $row['title'],
                'sort_order' => $row['sort_order'],
                'product_sku' => $row['product_sku'],
                'values' => $optionValues[$row['parent_id']][$row['option_id']] ?? [],
            ],
        ];
    }

    /**
     * Format bundle item values row
     *
     * @param array $row
     *
     * @return array
     */
    private function formatBundleValuesRow(array $row) : array
    {
        return [
            'id' => $row['id'],
            'label' => $row['label'],
            'quantity' => $row['quantity'],
            'sort_order' => $row['sort_order'],
            'is_default' => $row['is_default'],
            'price' => [
                'regularPrice' => $row['price'],
                'finalPrice' => $row['price'],
            ],
            'price_type' => $this->priceTypes[$row['price_type']] ?? 'DYNAMIC',
            'can_change_quantity' => $row['can_change_quantity'],
            'entity_id' => $row['entity_id'],
        ];
    }
}
