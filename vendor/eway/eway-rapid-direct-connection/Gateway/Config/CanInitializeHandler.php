<?php
namespace Eway\DirectConnection\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class CanInitializeHandler implements ValueHandlerInterface
{

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        // Direct Connection does not support initialize command
        return 0;
    }
}
