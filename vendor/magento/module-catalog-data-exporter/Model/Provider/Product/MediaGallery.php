<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product;

use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\CatalogDataExporter\Model\Query\MediaGalleryQueryBuilder;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Product media gallery data provider
 */
class MediaGallery
{
    /**
     * Image codes mapping
     *
     * @var string[]
     */
    private static $imagesMapping = [
        'image' => 'image',
        'smallImage' => 'small_image',
        'thumbnail' => 'thumbnail',
        'swatchImage' => 'swatch_image',
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MediaGalleryQueryBuilder
     */
    private $mediaGalleryQueryBuilder;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MediaGalleryQueryBuilder $mediaGalleryQueryBuilder
     * @param MediaConfig $mediaConfig
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MediaGalleryQueryBuilder $mediaGalleryQueryBuilder,
        MediaConfig $mediaConfig,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mediaGalleryQueryBuilder = $mediaGalleryQueryBuilder;
        $this->mediaConfig = $mediaConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
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
        $queryArguments = [];
        $output = [];
        $defaultImages = [];

        foreach ($values as $value) {
            $queryArguments[$value['storeViewCode']][$value['productId']] = $value['productId'];

            foreach (self::$imagesMapping as $key => $code) {
                $defaultImages[$value['storeViewCode']][$value['productId']][$code] = $value[$key . '_default'] ?? '';
            }
        }

        try {
            $connection = $this->resourceConnection->getConnection();
            $baseMediaUrls = $this->prepareBaseMediaUrlsByStoreViewCodes(\array_keys($queryArguments));

            foreach ($queryArguments as $storeViewCode => $productIds) {
                $cursor = $connection->query($this->mediaGalleryQueryBuilder->getQuery($productIds, $storeViewCode));

                while ($row = $cursor->fetch()) {
                    $output[] = $this->format($row, $defaultImages, $baseMediaUrls);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData('Unable to retrieve product media gallery');
        }

        return $output;
    }

    /**
     * Format provider data
     *
     * @param array $row
     * @param array $defaultImagesArray
     * @param string[] $baseMediaUrls
     *
     * @return array
     */
    private function format(array $row, array $defaultImagesArray, array $baseMediaUrls) : array
    {
        $defaultImages = $defaultImagesArray[$row['storeViewCode']][$row['productId']];

        return [
            'productId' => $row['productId'],
            'storeViewCode' => $row['storeViewCode'],
            'media_gallery' => [
                'url' => $baseMediaUrls[$row['storeViewCode']] . $row['file'],
                'label' => $row['label'] ?? '',
                'types' => \array_keys(\array_filter($defaultImages), $row['file'], true),
                'sort_order' => (int)$row['sortOrder'],
                'video_attributes' => $this->getVideoContent($row),
            ],
        ];
    }

    /**
     * Prepare base media urls by store view codes
     *
     * @param string[] $storeViewCodes
     *
     * @return string[]
     *
     * @throws NoSuchEntityException
     */
    private function prepareBaseMediaUrlsByStoreViewCodes(array $storeViewCodes) : array
    {
        $urls = [];

        foreach ($storeViewCodes as $storeViewCode) {
            $urls[$storeViewCode] = \sprintf(
                '%s%s',
                $this->storeManager->getStore($storeViewCode)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
                $this->mediaConfig->getBaseMediaPath()
            );
        }

        return $urls;
    }

    /**
     * Get video content
     *
     * @param array $row
     *
     * @return array|null
     */
    private function getVideoContent(array $row) : ?array
    {
        $videoContent = \array_filter($row, function ($value, $field) {
            return !empty($value) && \strpos($field, 'video') === 0;
        }, ARRAY_FILTER_USE_BOTH);

        if ($videoContent) {
            $videoContent['mediaType'] = $row['mediaType'];
        }

        return $videoContent ?: null;
    }
}
