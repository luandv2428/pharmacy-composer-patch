<?php
namespace Eway\SharedPage\Model\Config\Source;

class SharedPageTheme implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => 'Default'
            ],
            [
                'value' => 'Bootstrap',
                'label' => 'Bootstrap'
            ],
            [
                'value' => 'BootstrapAmelia',
                'label' => 'Bootstrap Amelia'
            ],
            [
                'value' => 'BootstrapCerulean',
                'label' => 'Bootstrap Cerulean'
            ],
            [
                'value' => 'BootstrapCosmo',
                'label' => 'Bootstrap Cosmo'
            ],
            [
                'value' => 'BootstrapCyborg',
                'label' => 'Bootstrap Cyborg'
            ],
            [
                'value' => 'BootstrapFlatly',
                'label' => 'Bootstrap Flatly'
            ],
            [
                'value' => 'BootstrapJournal',
                'label' => 'Bootstrap Journal'
            ],
            [
                'value' => 'BootstrapReadable',
                'label' => 'Bootstrap Readable'
            ],
            [
                'value' => 'BootstrapSimplex',
                'label' => 'Bootstrap Simplex'
            ],
            [
                'value' => 'BootstrapSlate',
                'label' => 'Bootstrap Slate'
            ],
            [
                'value' => 'BootstrapSpacelab',
                'label' => 'Bootstrap Spacelab'
            ],
            [
                'value' => 'BootstrapUnited',
                'label' => 'Bootstrap United'
            ]
        ];
    }
}
