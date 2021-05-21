<?php
namespace Eway\EwayRapid\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /** @var \Magento\Eav\Setup\EavSetupFactory */
    protected $eavSetupFactory; // @codingStandardsIgnoreLine

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $setupFactory
    ) {
        $this->eavSetupFactory = $setupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'saved_tokens',
                [
                    'type'         => 'text',
                    'input'        => 'hidden',
                    'label'        => '',
                    'required'     => 0,
                    'user_defined' => 0,
                    'visible'      => 0,
                    'backend'      => 'Eway\EwayRapid\Model\Customer\Token\Backend',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'ewayrapid_is_blocked',
                [
                    'type'         => 'int',
                    'input'        => 'hidden',
                    'label'        => '',
                    'required'     => 0,
                    'user_defined' => 0,
                    'visible'      => 0,
                ]
            );
        }

        $setup->endSetup();
    }
}
