<?php

/**
 * Http Client service
 */

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use DvsaLogger\Logger\MotLogger;
use Laminas\Uri\Http;
use Traversable;

/**
 * Http Client service
 */
abstract class AbstractHttpClientService implements HttpClientServiceInterface
{
    protected Client $client;

    protected Request $request;

    /**
     * Holds the logger object
     */
    protected MotLogger $logger;

    protected string $domainUrl;

    #[\Override]
    public function setClient(Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    #[\Override]
    public function setLogger(MotLogger $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    #[\Override]
    public function setRequest(Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    #[\Override]
    public function getClient(): Client
    {
        return $this->client;
    }

    #[\Override]
    public function getLogger(): MotLogger
    {
        return $this->logger;
    }

    #[\Override]
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Wrapper method to set the request URI
     *
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    #[\Override]
    public function setUri(string|Http $uri): static
    {
        $this->request->setUri($uri);
        return $this;
    }

    /**
     * Wrapper method to set the request content
     *
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    #[\Override]
    public function setContent(mixed $content): static
    {
        $this->request->setContent($content);
        return $this;
    }

    /**
     * Wrapper method to set any client options
     *
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    #[\Override]
    public function setOptions(array|Traversable $options): static
    {
        $this->client->setOptions($options);
        return $this;
    }

    #[\Override]
    abstract public function dispatch(): Response;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue BL-21801
     */
    #[\Override]
    abstract public function setDomainUrl(string $domainUrl): static;

    #[\Override]
    abstract public function getDomainUrl(): string;
}
