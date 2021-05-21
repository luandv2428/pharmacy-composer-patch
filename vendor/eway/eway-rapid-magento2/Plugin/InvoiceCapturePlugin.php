<?php
namespace Eway\EwayRapid\Plugin;

use Magento\Framework\Registry;

class InvoiceCapturePlugin
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function beforeExecute(\Magento\Framework\App\Action\Action $subject) //@codingStandardsIgnoreLine
    {
        $this->registry->register('ewayrapid_should_verify_fraud', true);
    }
}
