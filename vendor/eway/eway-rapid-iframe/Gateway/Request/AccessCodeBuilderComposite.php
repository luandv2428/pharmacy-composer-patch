<?php
namespace Eway\IFrame\Gateway\Request;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\ConfigInterface;

class AccessCodeBuilderComposite extends \Eway\EwayRapid\Gateway\Request\BuilderComposite
{
    public function __construct(
        TMapFactory $tmapFactory,
        ConfigInterface $config,
        $method = '',
        array $builders = []
    ) {
        if ($method == '') {
            $connectionType = $config->getValue('connection_type');
            switch ($connectionType) {
                case 'iframe':
                case 'sharedpage':
                    $method = \Eway\Rapid\Enum\ApiMethod::RESPONSIVE_SHARED;
                    break;
                case 'transparent':
                    $method = \Eway\Rapid\Enum\ApiMethod::TRANSPARENT_REDIRECT;
                    break;
            }
        }

        parent::__construct($tmapFactory, $method, $builders);
    }
}
