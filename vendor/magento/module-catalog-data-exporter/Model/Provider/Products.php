<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider;

use Magento\CatalogDataExporter\Model\Provider\EavAttributes\EavAttributesProvider;
use Magento\CatalogDataExporter\Model\Provider\Product\Formatter\FormatterInterface;
use Magento\CatalogDataExporter\Model\Query\ProductMainQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Products data provider
 */
class Products
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavAttributesProvider
     */
    private $eavAttributesProvider;

    /**
     * @var ProductMainQuery
     */
    private $productMainQuery;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param EavAttributesProvider $eavAttributesProvider
     * @param ProductMainQuery $productMainQuery
     * @param FormatterInterface $formatter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavAttributesProvider $eavAttributesProvider,
        ProductMainQuery $productMainQuery,
        FormatterInterface $formatter,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavAttributesProvider = $eavAttributesProvider;
        $this->productMainQuery = $productMainQuery;
        $this->formatter = $formatter;
        $this->logger = $logger;
    }

    /**
     * Get provider data
     *
     * @param array $values
     *
     * @return array
     *
     * @throws UnableRetrieveData
     */
    public function get(array $values) : array
    {
        $output = [];
        $queryArguments = [];
        $mappedProducts = [];

        try {
            foreach ($values as $value) {
                $queryArguments['productId'][$value['productId']] = $value['productId'];
            }

            $connection = $this->resourceConnection->getConnection();
            $cursor = $connection->query($this->productMainQuery->getQuery($queryArguments));

            while ($row = $cursor->fetch()) {
                $mappedProducts[$row['storeViewCode']][$row['productId']] = $row;
            }

            foreach ($mappedProducts as $storeCode => $products) {
                $output[] = \array_map(function ($row) {
                    return $this->formatter->format($row);
                }, \array_replace_recursive(
                    $products,
                    $this->eavAttributesProvider->getEavAttributesData(\array_keys($products), $storeCode)
                ));
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve product data');
        }

        return !empty($output) ? \array_merge(...$output) : [];
    }
}
