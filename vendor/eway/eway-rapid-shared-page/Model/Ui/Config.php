<?php
namespace Eway\SharedPage\Model\Ui;

class Config extends \Eway\IFrame\Model\Ui\Config
{
    const CONNECTION_TYPE = 'sharedpage';

    /**
     * @return string
     */
    public function getConnectionType()
    {
        return self::CONNECTION_TYPE;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Responsive Shared Page';
    }

    /**
     * @return string;
     */
    public function getMethodRendererPath()
    {
        return 'Eway_SharedPage/js/view/payment/method-renderer/sharedpage';
    }

    /**
     * @return string
     */
    public function getMycardFormBlock()
    {
        return 'EwayRapidSharedPageMycardForm';
    }
}
