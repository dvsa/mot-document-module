<?php

namespace DvsaReport\Service\Factory;

use Interop\Container\ContainerInterface;
use RuntimeException;
use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Http Client Service Factory
 */
class LambdaHttpClientFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $named
     * @param array|null $args
     * @return LambdaHttpClientService|object
     */
    public function __invoke(ContainerInterface $container, $named, array $args = null)
    {
        /** @var array */
        $config = $container->get('Config');

        if (!isset($config['certificate_generation'])) {
            throw new RuntimeException('Missing required certificate_generation configuration');
        }

        /** @var array */
        $config = $config['certificate_generation'];

        if (!isset($config['headers'])) {
            throw new RuntimeException('Missing required option certificate_generation.headers');
        }

        if (!isset($config['uri'])) {
            throw new RuntimeException('Missing required option certificate_generation.uri');
        }

        if (!isset($config['x-api-key'])) {
            throw new RuntimeException('Missing required option certificate_generation.x-api-key');
        }

        $service = new LambdaHttpClientService();

        /** @var string */
        $xApiKey = $config['x-api-key'];
        /** @var array */
        $configHeaders = $config['headers'];

        $request = new Request();
        /** @var \Laminas\Http\Headers $headers */
        $headers = $request->getHeaders();
        $headers ->addHeaderLine('x-api-key', $xApiKey)
                    ->addHeaders($configHeaders);
        $request = $request->setHeaders($headers);

        /** @var string */
        $clientAdapter = $config['adapter'];
        /** @var string */
        $clientUri = $config['uri'];

        $service->setClient(new Client())
            ->setRequest($request)
            ->setAdapter($clientAdapter)
            ->setDomainUrl($clientUri);

        if (isset($config['client_options'])) {
            /** @var array */
            $clientOptions = $config['client_options'];
            $service->setOptions($clientOptions);
        }

        return $service;
    }
}
