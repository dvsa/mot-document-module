<?php

/**
 * Http Client service
 */

namespace DvsaReport\Service\HttpClient;

use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Log\Logger;
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
     * @var \Laminas\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $domainUrl;

    /**
     * Set the client object
     *
     * @param \Laminas\Http\Client $client
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
     * @param \Laminas\Log\Logger $logger
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
     * @param \Laminas\Http\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Laminas\Http\Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return \Laminas\Log\Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return \Laminas\Http\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Wrapper method to set the request URI
     *
     * @param string|\Laminas\Uri\Http $uri
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
