<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Staging\Block\Adminhtml\Update\Entity;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Staging\Block\Adminhtml\Update\IdProvider as UpdateIdProvider;
use Magento\Staging\Model\Preview\UrlBuilder;

/**
 * Class PreviewButton
 */
class PreviewButton implements ButtonProviderInterface
{
    /**
     * @var EntityProviderInterface
     */
    protected $entityProvider;

    /**
     * @var UpdateIdProvider
     */
    protected $updateIdProvider;

    /**
     * @var UrlBuilder
     */
    protected $previewUrlBuilder;

    /**
     * PreviewButton constructor.
     *
     * @param EntityProviderInterface $entityProvider
     * @param UpdateIdProvider $updateIdProvider
     * @param UrlBuilder $previewUrlBuilder
     */
    public function __construct(
        EntityProviderInterface $entityProvider,
        UpdateIdProvider $updateIdProvider,
        UrlBuilder $previewUrlBuilder
    ) {
        $this->entityProvider = $entityProvider;
        $this->updateIdProvider = $updateIdProvider;
        $this->previewUrlBuilder = $previewUrlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->updateIdProvider->getUpdateId()) {
            $url = $this->previewUrlBuilder->getPreviewUrl(
                $this->updateIdProvider->getUpdateId(),
                $this->entityProvider->getUrl($this->updateIdProvider->getUpdateId())
            );

            $data = [
                'label' => __('Preview'),
                'url' =>  $url,
                'on_click' => "window.open('" . $url . "','_blank')",
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
