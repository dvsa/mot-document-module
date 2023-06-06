<?php

use DvsaDocument\Service\Document\DocumentService;

return [
    'doctrine' => [
        'driver' => [
            'document_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../module/DvsaDocument/Entity'
                ]
            ],
            'orm_default' => [
                'drivers' => [
                    'DvsaDocument\Entity' => 'document_entities',
                ]
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
            DocumentService::class => \DvsaDocument\Factory\Service\DocumentServiceFactory::class
        ]
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            \DvsaDocument\Controller\DocumentController::class =>\DvsaDocument\Factory\Controller\DocumentControllerFactory::class,
            \DvsaDocument\Controller\ReportNameController::class => \DvsaDocument\Factory\Controller\ReportNameControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'GetReportName' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/get-report-name[/:id][/:variation][/]',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'variation' => '[^/]+'
                    ],
                    'defaults' => [
                        'controller' => 'ReportNameController',
                        'action' => 'get'
                    ]
                ]
            ],
            'CreateDocument' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/create-document[/]',
                    'defaults' => [
                        'controller' => 'DocumentController',
                        'action' => 'create'
                    ]
                ]
            ],
            'DeleteDocument' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/delete-document[/:id][/]',
                    'constraints' => [
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'DocumentController',
                        'action' => 'delete'
                    ]
                ]
            ],
        ]
    ]
];
