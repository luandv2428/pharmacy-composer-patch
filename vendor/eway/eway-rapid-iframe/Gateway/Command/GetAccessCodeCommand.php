<?php
namespace Eway\IFrame\Gateway\Command;

use Eway\EwayRapid\Gateway\Command\GenericCommand;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class GetAccessCodeCommand extends GenericCommand
{
    /** @var ArrayResultFactory */
    protected $arrayResultFactory; // @codingStandardsIgnoreLine

    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        ArrayResultFactory $arrayResultFactory,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null
    ) {
    
        parent::__construct($requestBuilder, $transferFactory, $client, $logger, $handler, $validator);
        $this->arrayResultFactory = $arrayResultFactory;
    }

    public function execute(array $commandSubject)
    {
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->client->placeRequest($transferO);
        return $this->arrayResultFactory->create(['array' => $response]);
    }
}
