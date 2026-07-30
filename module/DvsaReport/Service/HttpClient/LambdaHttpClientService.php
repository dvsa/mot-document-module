<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Response;
use Laminas\Http\Client\Adapter\AdapterInterface;

/**
 * Http Client service
 */
class LambdaHttpClientService extends AbstractHttpClientService
{
    /**
     * Wrapper method to dispatch the request and return the response
     */
    #[\Override]
    public function dispatch(): Response
    {
        $this->logger->info('Lambda document generator service call. Url: "' . $this->request->getUriString() . '"');

        /** @var Response $response */
        $response = $this->client->dispatch($this->request);

        return $response;
    }

    #[\Override]
    public function setDomainUrl(string $domainUrl): static
    {
        $this->domainUrl = $domainUrl;
        return $this;
    }

    #[\Override]
    public function getDomainUrl(): string
    {
        return $this->domainUrl;
    }

    /**
     * Wrapper method to set any client adapter
     */
    public function setAdapter(AdapterInterface|string $adapter): static
    {
        $this->client->setAdapter($adapter);
        return $this;
    }
}
