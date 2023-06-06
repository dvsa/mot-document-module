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
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;

class EnhancedLambdaHttpClientServiceFactory implements FactoryInterface
{
    const REQUIRED_CONFIG_ROOT_KEY = 'certificate_generation';
    const REQUIRED_CONFIG_KEYS = ['headers', 'uri', 'x-api-key', 'max_request_attempt_count', 'request_attempt_delay'];

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $args
     * @return EnhancedLambdaHttpClientService|object
     */
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        $config = $container->get('Config');
        $this->verifyConfigKey($config, self::REQUIRED_CONFIG_ROOT_KEY);

        $config = $config[self::REQUIRED_CONFIG_ROOT_KEY];
        $this->verifyConfig($config);

        $logger = $this->obtainLogger($container);

        $service = new EnhancedLambdaHttpClientService($config['max_request_attempt_count'], $config['request_attempt_delay']);

        $request = new Request();
        $headers = $request->getHeaders()
            ->addHeaderLine('x-api-key', $config['x-api-key'])
            ->addHeaders($config['headers']);
        $request = $request->setHeaders($headers);

        $service->setClient(new TraceableHttpClient(new RequestTracingService($logger)))
            ->setRequest($request)
            ->setAdapter($config['adapter'])
            ->setDomainUrl($config['uri']);

        if (isset($config['client_options'])) {
            $service->setOptions($config['client_options']);
        }

        $service->setLogger($logger);

        return $service;

    }

    protected function verifyConfig($config) {
        foreach ( self::REQUIRED_CONFIG_KEYS as $configKey) {
            $this->verifyConfigKey($config, $configKey);
        }
    }

    protected function verifyConfigKey($config, $configKey) {
        if (!isset($config[$configKey])) {
            throw new RuntimeException('Missing required' . $configKey . 'configuration');
        }
    }

    protected function obtainLogger($serviceLocator) {
        $logger = null;
        try {
            $logger = $serviceLocator->get('Application\Logger');
        }  catch (\Laminas\ServiceManager\Exception\ServiceNotFoundException $e) { }

        if (!is_callable(array($logger, 'log'), true)) {
            throw new RuntimeException('\Laminas\Log\Logger instance expected in ServiceLocator under Application\Logger');
        }

        return $logger;
    }
}