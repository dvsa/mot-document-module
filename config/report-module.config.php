<?php

use DvsaReport\Service\Factory\LambdaHttpClientFactory;
use DvsaReport\Service\Factory\EnhancedLambdaHttpClientServiceFactory;
use DvsaReport\Service\Factory\LambdaReportFactory;
use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use DvsaReport\Service\Pdf\PdfRenderer;
use DvsaReport\Service\Pdf\PdfRendererFactory;
use DvsaReport\Service\Report\LambdaReportService;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;

return [
    'service_manager' => [
        'factories' => [
            LambdaHttpClientService::class => LambdaHttpClientFactory::class,
            'ReportService'     => '\DvsaReport\Service\Factory\ReportFactory',
            LambdaReportService::class => LambdaReportFactory::class,
            PdfRenderer::class  => PdfRendererFactory::class,
            EnhancedLambdaHttpClientService::class => EnhancedLambdaHttpClientServiceFactory::class
        ]
    ],
    'certificate_generation' => [
        'adapter' => 'Laminas\Http\Client\Adapter\Curl',
        'headers'        => [
            'Content-Type' => 'application/json',
        ],
        'max_request_attempt_count' => 3,
        'request_attempt_delay' => 2 // subsequent requests delayed by pattern: attempt_nr * request_attempt_delay
    ],
    'report_builder' => [
        'html_to_pdf_binary' => '/usr/bin/wkhtmltopdf'
    ]
];
