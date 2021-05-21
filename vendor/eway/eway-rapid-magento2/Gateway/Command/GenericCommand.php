<?php
namespace Eway\EwayRapid\Gateway\Command;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class GenericCommand extends \Magento\Payment\Gateway\Command\GatewayCommand
{
    protected $requestBuilder; // @codingStandardsIgnoreLine
    protected $transferFactory; // @codingStandardsIgnoreLine
    protected $client; // @codingStandardsIgnoreLine
    protected $logger; // @codingStandardsIgnoreLine
    protected $handler; // @codingStandardsIgnoreLine
    protected $validator; // @codingStandardsIgnoreLine

    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null
    ) {
    
        parent::__construct($requestBuilder, $transferFactory, $client, $logger, $handler, $validator);
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->logger = $logger;
        $this->handler = $handler;
        $this->validator = $validator;
    }
}
