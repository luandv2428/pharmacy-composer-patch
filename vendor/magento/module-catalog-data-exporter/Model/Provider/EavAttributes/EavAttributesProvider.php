<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\EavAttributes;

use Magento\CatalogDataExporter\Model\Query\Eav\EavAttributeQueryBuilderInterface;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\DataExporter\Sql\FieldToPropertyNameConverter;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Eav attributes data provider
 */
class EavAttributesProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AttributesDataConverter
     */
    private $attributesDataConverter;

    /**
     * @var FieldToPropertyNameConverter
     */
    private $nameConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EavAttributeQueryBuilderInterface
     */
    private $queryBuilder;

    /**
     * @var array
     */
    private $includeAttributes;

    /**
     * @param AttributesDataConverter $attributesDataConverter
     * @param ResourceConnection $resourceConnection
     * @param FieldToPropertyNameConverter $nameConverter
     * @param LoggerInterface $logger
     * @param EavAttributeQueryBuilderInterface $queryBuilder
     * @param array $includeAttributes
     */
    public function __construct(
        AttributesDataConverter $attributesDataConverter,
        ResourceConnection $resourceConnection,
        FieldToPropertyNameConverter $nameConverter,
        LoggerInterface $logger,
        EavAttributeQueryBuilderInterface $queryBuilder,
        array $includeAttributes = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributesDataConverter = $attributesDataConverter;
        $this->nameConverter = $nameConverter;
        $this->logger = $logger;
        $this->queryBuilder = $queryBuilder;
        $this->includeAttributes = $includeAttributes;
    }

    /**
     * Get converted eav attributes data
     *
     * @param int[] $entityIds
     * @param string $storeCode
     *
     * @return array
     *
     * @throws UnableRetrieveData
     */
    public function getEavAttributesData(array $entityIds, string $storeCode) : array
    {
        try {
            $attributes = $this->resourceConnection->getConnection()->fetchAll(
                $this->queryBuilder->build($entityIds, $this->includeAttributes, $storeCode)
            );

            return \array_map(function ($data) {
                return $this->formatEavAttributesArray($data);
            }, $this->attributesDataConverter->convert($attributes));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve category eav attributes');
        }
    }

    /**
     * Format eav attributes array
     *
     * @param array $array
     *
     * @return array
     */
    private function formatEavAttributesArray(array $array) : array
    {
        $includeAttributes = [];

        foreach ($this->includeAttributes as $attribute) {
            $includeAttributes[$this->nameConverter->toCamelCase($attribute)] = $array[$attribute] ?? null;
        }

        return $includeAttributes;
    }
}
