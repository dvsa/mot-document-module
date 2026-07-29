<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use DvsaLogger\Logger\MotLogger;
use Traversable;

interface HttpClientServiceInterface
{
    /**
     * @return static
     */
    public function setClient(Client $client);

    /**
     * @return static
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setLogger(MotLogger $logger);

    /**
     * @return static
     */
    public function setRequest(Request $request);

    /**
     * @return static
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setUri(string|\Laminas\Uri\Http $uri);

    /**
     * @return static
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setContent(mixed $content);

    /**
     * @return static
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setOptions(array|Traversable $options);

    /**
     * @return static
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setDomainUrl(string $domainUrl);

    public function getClient(): Client;

    public function getLogger(): MotLogger;

    public function getRequest(): Request;

    public function dispatch(): Response;

    public function getDomainUrl(): string;
}
