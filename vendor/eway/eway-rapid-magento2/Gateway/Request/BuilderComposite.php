<?php
namespace Eway\EwayRapid\Gateway\Request;

use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Request\BuilderComposite as DefaultBuilderComposite;

class BuilderComposite extends DefaultBuilderComposite
{
    const METHOD = 'ApiMethod';

    /** @var string */
    protected $method; // @codingStandardsIgnoreLine

    public function __construct(
        TMapFactory $tmapFactory,
        $method = '',
        array $builders = []
    ) {
        parent::__construct($tmapFactory, $builders);
        $this->method = $method;
    }

    protected function merge(array $result, array $builder) // @codingStandardsIgnoreLine
    {
        $result = array_replace_recursive($result, $builder);
        $result[self::METHOD] = $this->method;

        return $result;
    }
}
