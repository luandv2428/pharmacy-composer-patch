<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Indexer;

/**
 * @inheritdoc
 */
class EmptyIndexerCallback implements ProductIndexerCallbackInterface, CategoryIndexerCallbackInterface
{
    /**
     * @inheritdoc
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function execute(array $ids) : void
    {
    }
}
