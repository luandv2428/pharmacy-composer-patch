<?php
namespace Eway\EwayRapid\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

class OrderMassAction extends \Magento\Ui\Component\MassAction
{
    /**
     * @var \Eway\EwayRapid\Model\ModuleHelper
     */
    private $moduleHelper;

    public function __construct(
        ContextInterface $context,
        \Eway\EwayRapid\Model\ModuleHelper $moduleHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->moduleHelper = $moduleHelper;
    }

    public function getChildComponents()
    {
        foreach ($this->components as $key => $child) {
            if ($child->getName() == 'ewayrapid_verify_order' && !$this->moduleHelper->isActive()) {
                unset($this->components[$key]);
            }
        }

        return $this->components;
    }
}
