<?php
namespace Eway\EwayRapid\Model;

use Magento\Framework\Component\ComponentRegistrar;

class Version
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var array
     */
    private $components;

    private $magentoVersion;
    private $ewayVersion;
    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    public function __construct(
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        $components = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->components = $components;
        $this->componentRegistrar = $componentRegistrar;
    }

    public function getMagentoVersion()
    {
        if (!$this->magentoVersion) {
            $this->magentoVersion = sprintf(
                '%s %s %s',
                $this->productMetadata->getName(),
                $this->productMetadata->getEdition(),
                $this->productMetadata->getVersion()
            );
        }

        return $this->magentoVersion;
    }

    public function getEwayVersion()
    {
        if (!$this->ewayVersion) {
            $components = [];
            foreach ($this->components as $component => $name) {
                $components[] = sprintf('%s: %s', $name, $this->getComponentVersion($component));
            }

            $this->ewayVersion = implode(', ', $components);
        }

        return $this->ewayVersion;
    }

    protected function getComponentVersion($component) //@codingStandardsIgnoreLine
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $component);
        $info = json_decode(file_get_contents($modulePath . '/composer.json')); //@codingStandardsIgnoreLine
        return $info->version;
    }
}
