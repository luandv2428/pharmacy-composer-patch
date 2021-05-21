<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\BundleProductDataExporter\Test\Integration;

use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for bundle product export
 */
class BundleProductTest extends AbstractProductTestHelper
{
    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->arrayUtils = Bootstrap::getObjectManager()->create(ArrayUtils::class);

        parent::setUp();
    }

    /**
     * Validate bundle product options data
     *
     * @param array $bundleProductOptionsDataProvider
     *
     * @magentoDataFixture Magento/Bundle/_files/product_1.php
     * @dataProvider getBundleProductOptionsDataProvider
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testBundleProductOptions(array $bundleProductOptionsDataProvider) : void
    {
        $this->runIndexer();

        $extractedProduct = $this->getExtractedProduct('bundle-product', 'default');
        $this->assertNotEmpty($extractedProduct, 'Feed data must not be empty');

        foreach ($bundleProductOptionsDataProvider as $key => $expectedData) {
            $diff = $this->arrayUtils->recursiveDiff($expectedData, $extractedProduct[$key]);
            self::assertEquals([], $diff, 'Actual feed data doesn\'t equal to expected data');
        }
    }

    /**
     * Get bundle product options data provider
     *
     * @return array
     */
    public function getBundleProductOptionsDataProvider() : array
    {
        return [
            'bundleProduct' => [
                'item' => [
                    'feedData' => [
                        'sku' => 'bundle-product',
                        'storeViewCode' => 'default',
                        'name' => 'Bundle Product',
                        'type' => 'bundle',
                        'options' => [
                            [
                                'type' => 'super',
                                'render_type' => 'select',
                                'is_required' => true,
                                'title' => 'Bundle Product Items',
                                'sort_order' => 0,
                                'product_sku' => 'bundle-product',
                                'values' => [
                                    [
                                        'price_type' => 'FIXED',
                                        'price' => [
                                            'regularPrice' => 2.75,
                                            'finalPrice' => 2.75,
                                        ],
                                        'sort_order' => 0,
                                        'label' => 'Simple Product',
                                        'quantity' => 1,
                                        'is_default' => false,
                                        'can_change_quantity' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
