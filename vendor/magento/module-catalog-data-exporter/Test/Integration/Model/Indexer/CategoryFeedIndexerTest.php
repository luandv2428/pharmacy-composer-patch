<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration\Model\Indexer;

use Magento\CatalogDataExporter\Model\Feed\Categories;
use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test class for category feed provider
 */
class CategoryFeedIndexerTest extends TestCase
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Categories
     */
    private $categoryFeed;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFeed = $this->objectManager->create(Categories::class);
    }

    /**
     * Test that feed returns the expected category
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testGetByIds() : void
    {
        $this->indexer->load(CategoryFeedIndexer::INDEXER_ID)->reindexList([333]);

        $feed = $this->categoryFeed->getFeedByIds(['333'])['feed'];

        $this->assertTrue(isset($feed[0]), 'Feed doesn\'t return the expected category');
        $this->assertEquals('333', $feed[0]['categoryId']);
    }
}
