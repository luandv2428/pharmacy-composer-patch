<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogDataExporter\Model\Indexer;

/**
 * Allows to perform an action after category feed index is updated
 */
interface CategoryIndexerCallbackInterface
{
    /**
     * Execute callback
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function execute(array $ids) : void;
}
