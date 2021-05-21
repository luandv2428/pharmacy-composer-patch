<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\CatalogDataExporter\Model\Feed\Categories;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Indexer\Model\Indexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Abstract class for category feed tests
 */
abstract class AbstractCategoryTest extends TestCase
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Categories
     */
    protected $categoryFeed;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->resource = Bootstrap::getObjectManager()->create(ResourceConnection::class);
        $this->connection = $this->resource->getConnection();
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->jsonSerializer = Bootstrap::getObjectManager()->create(Json::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->categoryFeed = Bootstrap::getObjectManager()->create(Categories::class);
    }

    /**
     * Run the indexer to extract categories data
     *
     * @return void
     */
    protected function runIndexer(array $ids) : void
    {
        $this->indexer->load(CategoryFeedIndexer::INDEXER_ID);
        $this->indexer->reindexList($ids);
    }

    /**
     * Assert base category data
     *
     * @param CategoryInterface $category
     * @param array $extract
     * @param string $storeViewCode
     */
    protected function assertBaseCategoryData(CategoryInterface $category, array $extract, string $storeViewCode) : void
    {
        $this->assertEquals($storeViewCode, $extract['storeViewCode']);
        $this->assertEquals($category->getId(), $extract['categoryId']);
        $this->assertEquals($category->getIsActive(), $extract['isActive']);
        $this->assertEquals($category->getName(), $extract['name']);
        $this->assertEquals($category->getPath(), $extract['path']);
        $this->assertEquals($category->getPathInStore(), $extract['pathInStore']);
        $this->assertEquals($category->getUrlKey(), $extract['urlKey']);
        $this->assertEquals($category->getUrlPath(), $extract['urlPath']);
        $this->assertEquals($category->getPosition(), $extract['position']);
        $this->assertEquals($category->getLevel(), $extract['level']);
        $this->assertEquals($category->getParentId(), $extract['parentId']);
        $this->assertEquals($category->getCreatedAt(), $extract['createdAt']);
        $this->assertEquals($category->getUpdatedAt(), $extract['updatedAt']);
        $this->assertEquals($category->getDefaultSortBy(), $extract['defaultSortBy']);
        $this->assertEquals($category->getImageUrl(), $extract['image']);
    }
}
