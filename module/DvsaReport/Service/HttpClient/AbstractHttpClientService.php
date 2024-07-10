<?php

/**
 * Http Client service
 */

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Log\Logger;
use Laminas\Uri\Http;
use Traversable;

/**
 * Http Client service
 */
class AbstractHttpClientService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Holds the logger object
     *
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $domainUrl;

    /**
     * Set the client object
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Set the logger
     *
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set the request object
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Wrapper method to set the request URI
     *
     * @param string|Http $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        $this->request->setUri($uri);
        return $this;
    }

    /**
     * Wrapper method to set the request URI
     *
     * @param mixed $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->request->setContent($content);
        return $this;
    }

    /**
     * Wrapper method to set any client options
     *
     * @param array|Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->client->setOptions($options);
        return $this;
    }
}
