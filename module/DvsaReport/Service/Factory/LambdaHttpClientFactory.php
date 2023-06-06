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
        $config = $container->get('Config');

        if (!isset($config['certificate_generation'])) {
            throw new RuntimeException('Missing required certificate_generation configuration');
        }

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

        $request = new Request();
        $headers = $request->getHeaders()
            ->addHeaderLine('x-api-key', $config['x-api-key'])
            ->addHeaders($config['headers']);
        $request = $request->setHeaders($headers);

        $service->setClient(new Client())
            ->setRequest($request)
            ->setAdapter($config['adapter'])
            ->setDomainUrl($config['uri']);

        if (isset($config['client_options'])) {
            $service->setOptions($config['client_options']);
        }

        return $service;
    }
}
