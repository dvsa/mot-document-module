<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Traversable;

interface HttpClientServiceInterface
{
    /**
     * @return static
     */
    public function setClient(Client $client);

    /**
     * @return static
     */
    public function setLogger(Logger $logger);

    /**
     * @return static
     */
    public function setRequest(Request $request);

    /**
     * @return static
     */
    public function setUri(string|\Laminas\Uri\Http $uri);

    /**
     * @return static
     */
    public function setContent(mixed $content);

    /**
     * @return static
     */
    public function setOptions(array|Traversable $options);

    /**
     * @return static
     */
    public function setDomainUrl(string $domainUrl);

    public function getClient(): Client;

    public function getLogger(): Logger;

    public function getRequest(): Request;

    public function dispatch(): Response;

    public function getDomainUrl(): string;
}
