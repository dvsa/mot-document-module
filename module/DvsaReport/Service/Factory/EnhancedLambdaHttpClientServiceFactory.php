<?php

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 20/02/2018
 * Time: 12:17
 */

namespace DvsaReport\Service\Factory;

use DvsaReport\Service\Tracing\RequestTracingService;
use Interop\Container\ContainerInterface;
use RuntimeException;
use DvsaReport\Service\HttpClient\TraceableHttpClient;
use Laminas\Http\Request;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use Laminas\Http\Headers;
use Laminas\Log\Logger;

class EnhancedLambdaHttpClientServiceFactory implements FactoryInterface
{
    public const REQUIRED_CONFIG_ROOT_KEY = 'certificate_generation';
    public const REQUIRED_CONFIG_KEYS = ['headers', 'uri', 'x-api-key', 'max_request_attempt_count', 'request_attempt_delay'];

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $args
     * @return EnhancedLambdaHttpClientService|object
     */
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        /** @var array */
        $config = $container->get('Config');
        $this->verifyConfigKey($config, self::REQUIRED_CONFIG_ROOT_KEY);

        /** @var array */
        $config = $config[self::REQUIRED_CONFIG_ROOT_KEY];
        $this->verifyConfig($config);

        $logger = $this->obtainLogger($container);

        /** @var int */
        $maxRequestAttemptCount = $config['max_request_attempt_count'];
        /** @var int */
        $requestAttemptDelay = $config['request_attempt_delay'];

        $service = new EnhancedLambdaHttpClientService($maxRequestAttemptCount, $requestAttemptDelay);

        /** @var string */
        $xApiKey = $config['x-api-key'];
        /** @var array */
        $configHeaders = $config['headers'];

        $request = new Request();
        /** @var Headers $headers */
        $headers = $request->getHeaders();
        $headers ->addHeaderLine('x-api-key', $xApiKey)
                    ->addHeaders($configHeaders);
        $request = $request->setHeaders($headers);

        /** @var string */
        $clientAdapter = $config['adapter'];
        /** @var string */
        $clientUri = $config['uri'];

        $service->setClient(new TraceableHttpClient(new RequestTracingService($logger)))
            ->setRequest($request)
            ->setAdapter($clientAdapter)
            ->setDomainUrl($clientUri);

        if (isset($config['client_options'])) {
            /** @var array */
            $clientOptions = $config['client_options'];
            $service->setOptions($clientOptions);
        }

        $service->setLogger($logger);

        return $service;
    }

    /**
     * @param array $config
     *
     * @return void
     */
    protected function verifyConfig($config)
    {
        foreach (self::REQUIRED_CONFIG_KEYS as $configKey) {
            $this->verifyConfigKey($config, $configKey);
        }
    }

    /**
     * @param array $config
     * @param string $configKey
     *
     * @return void
     */
    protected function verifyConfigKey($config, $configKey)
    {
        if (!isset($config[$configKey])) {
            throw new RuntimeException('Missing required' . $configKey . 'configuration');
        }
    }

    /**
     * @param ContainerInterface $serviceLocator
     *
     * @return Logger
     */
    protected function obtainLogger($serviceLocator): object
    {
        /** @var Logger|null */
        $logger = null;
        try {
            $logger = $serviceLocator->get('Application\Logger');
        } catch (ServiceNotFoundException $e) {
        }

        if (!is_callable(array($logger, 'log'), true) || !($logger instanceof Logger)) {
            throw new RuntimeException('\Laminas\Log\Logger instance expected in ServiceLocator under Application\Logger');
        }

        return $logger;
    }
}
