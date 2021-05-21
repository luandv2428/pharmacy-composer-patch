<?php
/**
 * Copyright © Bazaarvoice, Inc. All rights reserved.
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Bazaarvoice\Connector\Api\Data\Dcc\CatalogData;

/**
 * Interface CatalogProductBuilderInterface
 *
 * @package Bazaarvoice\Connector\Api\Data\Dcc\CatalogData
 */
interface CatalogProductBuilderInterface
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product      $product
     * @param null|\Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $parentProduct
     *
     * @return \Bazaarvoice\Connector\Api\Data\Dcc\CatalogData\CatalogProductInterface
     */
    public function build($product, $parentProduct = null);
}
