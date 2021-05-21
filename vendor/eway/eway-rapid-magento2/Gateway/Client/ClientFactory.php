<?php
namespace Eway\EwayRapid\Gateway\Client;

class ClientFactory
{
    public function create($apiKey, $apiPassword, $apiEndpoint, $logger = null)
    {
        $client = \Eway\Rapid::createClient($apiKey, $apiPassword, $apiEndpoint, $logger);
        $client->setVersion(40);
        return $client;
    }
}
