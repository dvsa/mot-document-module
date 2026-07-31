<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use DvsaLogger\Logger\MotLogger;
use Laminas\Uri\Http;
use Traversable;

interface HttpClientServiceInterface
{
    public function setClient(Client $client): static;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setLogger(MotLogger $logger): static;

    public function setRequest(Request $request): static;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setUri(string|Http $uri): static;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setContent(mixed $content): static;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setOptions(array|Traversable $options): static;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-22047
     */
    public function setDomainUrl(string $domainUrl): static;

    public function getClient(): Client;

    public function getLogger(): MotLogger;

    public function getRequest(): Request;

    public function dispatch(): Response;

    public function getDomainUrl(): string;
}
