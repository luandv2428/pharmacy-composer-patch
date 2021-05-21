<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Indexer;

/**
 * Allows to perform an action after index is updated
 */
interface ProductIndexerCallbackInterface
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
