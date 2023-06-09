<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Log\Logger;

interface HttpClientServiceInterface
{
    public function setClient(Client $client);

    public function setLogger(Logger $logger);

    public function setRequest(Request $request);

    public function setUri($uri);

    public function setContent($content);

    public function setOptions($options);

    public function setDomainUrl($domainUrl);

    public function getClient() : Client;

    public function getLogger() : Logger;

    public function getRequest() : Request;

    public function dispatch() : Response;

    public function getDomainUrl() : string;
}