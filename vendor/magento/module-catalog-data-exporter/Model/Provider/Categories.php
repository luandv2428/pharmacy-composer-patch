<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider;

use Magento\CatalogDataExporter\Model\Provider\Category\Formatter\FormatterInterface;
use Magento\CatalogDataExporter\Model\Provider\EavAttributes\EavAttributesProvider;
use Magento\CatalogDataExporter\Model\Query\CategoryMainQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Categories main data provider
 */
class Categories
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
     * @var CategoryMainQuery
     */
    private $categoryMainQuery;

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
     * @param CategoryMainQuery $categoryMainQuery
     * @param FormatterInterface $formatter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavAttributesProvider $eavAttributesProvider,
        CategoryMainQuery $categoryMainQuery,
        FormatterInterface $formatter,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavAttributesProvider = $eavAttributesProvider;
        $this->categoryMainQuery = $categoryMainQuery;
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
        $mappedCategories = [];

        try {
            foreach ($values as $value) {
                $queryArguments['categoryId'][$value['categoryId']] = $value['categoryId'];
            }

            $connection = $this->resourceConnection->getConnection();
            $cursor = $connection->query($this->categoryMainQuery->getQuery($queryArguments));

            while ($row = $cursor->fetch()) {
                $mappedCategories[$row['storeViewCode']][$row['categoryId']] = $row;
            }

            foreach ($mappedCategories as $storeCode => $categories) {
                $output[] = \array_map(function ($row) {
                    return $this->formatter->format($row);
                }, \array_replace_recursive(
                    $categories,
                    $this->eavAttributesProvider->getEavAttributesData(\array_keys($categories), $storeCode)
                ));
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve category data');
        }

        return !empty($output) ? \array_merge(...$output) : [];
    }
}
