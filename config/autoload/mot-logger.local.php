<?php

return [
    'mot_logger' => [
        'loggers' => [
            'mot-document-module' => [
                'channel' => 'mot-document-module',
                'writers' => [
                    [
                        'type' => 'stream',
                        'path' => 'php://stderr',
                        'formatter' => 'pipe',
                        'level' => 'error',
                        'enabled' => true,
                    ],
                ],
            ],
        ],
    ],
];
