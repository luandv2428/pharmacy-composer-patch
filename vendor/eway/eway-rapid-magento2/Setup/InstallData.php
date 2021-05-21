<?php
namespace Eway\EwayRapid\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    private $statusFactory;

    public function __construct(\Magento\Sales\Model\Order\StatusFactory $statusFactory)
    {
        $this->statusFactory = $statusFactory;
    }

    // @codingStandardsIgnoreLine
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $newStatuses = [
            'eway_authorised' => [
                'label' => 'eWAY Authorised',
                'state' => 'processing'
            ],
            'eway_captured'    => [
                'label' => 'eWAY Captured',
                'state' => 'processing'
            ],
        ];

        foreach ($newStatuses as $code => $data) {
            $status = $this->statusFactory->create();
            $status->setStatus($code)->setLabel($data['label']);
            // @codingStandardsIgnoreLine
            $status->save();
            $status->assignState($data['state'], false, true);
        }

        $installer->endSetup();
    }
}
