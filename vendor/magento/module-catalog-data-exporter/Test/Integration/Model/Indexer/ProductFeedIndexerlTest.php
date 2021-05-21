<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration\Model\Indexer;

class ProductFeedIndexerlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Products
     */
    private $productFeed;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productFeed = $this->objectManager->create(\Magento\CatalogDataExporter\Model\Feed\Products::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     */
    public function testGetByIds() : void
    {
        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $feed = $this->productFeed->getFeedByIds([$product->getId()])['feed'];

        $this->assertTrue(isset($feed[0]), 'Feed doesn\'t contain any products');
        $this->assertEquals($feed[0]['productId'], $product->getId());
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reindex()
    {
        $appDir = dirname(\Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppTempDir());
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex");
    }
}
