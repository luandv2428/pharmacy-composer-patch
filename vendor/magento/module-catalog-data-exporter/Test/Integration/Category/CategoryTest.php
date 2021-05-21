<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration\Category;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test class for category feed
 */
class CategoryTest extends AbstractCategoryTest
{
    /**
     * Validate categories in different store views
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/CatalogDataExporter/_files/setup_category_tree.php
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testCategoriesInDifferentStoreViews() : void
    {
        $this->runIndexer([400, 401, 402, 500, 501, 502]);

        $currentStore = $this->storeManager->getStore();
        $storeDefault = $this->storeManager->getStore('default');
        $storeCustomOne = $this->storeManager->getStore('custom_store_view_one');
        $storeCustomTwo = $this->storeManager->getStore('custom_store_view_two');

        try {
            foreach ([400, 401, 402] as $categoryId) {
                foreach ([$storeCustomOne, $storeCustomTwo] as $store) {
                    $this->storeManager->setCurrentStore($store);
                    $category = $this->categoryRepository->get($categoryId, $store->getId());
                    $extractedCategoryData = $this->categoryFeed->getFeedByIds(
                        [$categoryId],
                        [$store->getCode()]
                    )['feed'][0];

                    $this->assertBaseCategoryData($category, $extractedCategoryData, $store->getCode());
                }
            }

            foreach ([500, 501, 502] as $categoryId) {
                $this->storeManager->setCurrentStore($storeDefault);
                $category = $this->categoryRepository->get($categoryId, $storeDefault->getId());
                $extractedCategoryData = $this->categoryFeed->getFeedByIds(
                    [$categoryId],
                    [$storeDefault->getCode()]
                )['feed'][0];

                $this->assertBaseCategoryData($category, $extractedCategoryData, $storeDefault->getCode());
            }
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }
}
