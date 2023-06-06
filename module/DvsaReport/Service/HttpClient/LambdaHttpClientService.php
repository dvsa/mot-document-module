<?php

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Response;

/**
 * Http Client service
 */
class LambdaHttpClientService extends AbstractHttpClientService implements HttpClientServiceInterface
{

    /**
     * Wrapper method to dispatch the request and return the response
     */
    public function dispatch() : Response
    {
        if ($this->logger) {
            $this->logger->info('Lambda document generator service call. Url: "' . $this->request->getUriString() . '"');
        }

        /** @var Response $response */
        $response = $this->client->dispatch($this->request);

        return $response;
    }

    public function setDomainUrl($domainUrl)
    {
        $this->domainUrl = $domainUrl;
        return $this;
    }

    public function getDomainUrl() : string
    {
        return $this->domainUrl;
    }

    /**
     * Wrapper method to set any client adapter
     */
    public function setAdapter($adapter)
    {
        $this->client->setAdapter($adapter);
        return $this;
    }
}
