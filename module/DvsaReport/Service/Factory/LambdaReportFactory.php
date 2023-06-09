<?php

namespace DvsaReport\Service\Factory;

use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use DvsaReport\Service\Pdf\PdfRenderer;
use DvsaReport\Service\Report\LambdaReportService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LambdaReportFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $args
     * @return LambdaReportService|object
     */
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        $pdfRenderer = $container->get(PdfRenderer::class);

        $client = $container->get(EnhancedLambdaHttpClientService::class);

        $service = new LambdaReportService($pdfRenderer, $client);

        return $service;
    }
}
