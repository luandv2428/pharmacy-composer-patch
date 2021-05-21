<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product\Formatter;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Framework\App\State;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ImageFormatter
 */
class ImageFormatter
{
    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var array
     */
    private $images;

    /**
     * ImageFieldFormatter constructor.
     *
     * @param ImageFactory $imageFactory
     * @param ConfigInterface $presentationConfig
     * @param ParamsBuilder $imageParamsBuilder
     * @param StoreManagerInterface $storeManager
     * @param DesignLoader $designLoader
     * @param State $appState
     * @param array $images
     */
    public function __construct(
        ImageFactory $imageFactory,
        ConfigInterface $presentationConfig,
        ParamsBuilder $imageParamsBuilder,
        StoreManagerInterface $storeManager,
        DesignLoader $designLoader,
        State $appState,
        array $images = [
            'image' => 'product_base_image',
            'smallImage' => 'product_small_image',
            'thumbnail' => 'product_thumbnail_image',
            'swatchImage' => 'product_swatch_image_small'
        ]
    ) {
        $this->imageFactory = $imageFactory;
        $this->presentationConfig = $presentationConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->storeManager = $storeManager;
        $this->designLoader = $designLoader;
        $this->appState = $appState;
        $this->images = $images;
    }

    /**
     * Format provider data
     *
     * @param array $row
     * @return array
     * @throws \Exception
     */
    public function format(array $row) : array
    {
        $actualStoreCode = $this->storeManager->getStore()->getCode();
        $this->storeManager->setCurrentStore($row['storeViewCode']);
        try {
            foreach ($this->images as $imageKey => $imageValue) {
                if (isset($row[$imageKey])) {
                    $imageUrl = $this->appState->emulateAreaCode(
                        'frontend',
                        [$this, "getImageUrl"],
                        [$row[$imageKey], $imageValue]
                    );

                    $row[$imageKey] = [
                        'url' => $imageUrl,
                        'label' => isset($row[$imageKey . '_label']) ? $row[$imageKey . '_label'] : null
                    ];
                }
            }
        } finally {
            $this->storeManager->setCurrentStore($actualStoreCode);
        }
        return $row;
    }

    /**
     * Get the product image url for a given image type
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    public function getImageUrl(string $path, string $type) : string
    {
        $this->designLoader->load();
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $type
        );
        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $asset = $this->imageFactory->create(
            [
                'miscParams' => $imageMiscParams,
                'filePath' => $path
            ]
        );
        $url = preg_replace('#^http(s)?:#', '', $asset->getUrl());
        return $url;
    }
}
