<?php
namespace Eway\EwayRapid\Model\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    public function __construct(DriverInterface $filesystem, $fileName, $filePath = null)
    {
        $this->fileName = $fileName;
        parent::__construct($filesystem, $filePath);
    }
}
