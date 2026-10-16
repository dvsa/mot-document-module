<?php

return [
    'modules' => [
        'DvsaLogger',
        'DvsaDocument',
        'DvsaReport',
    ],
    'module_listener_options' => [
        'module_paths' => [
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
        ],
        'config_glob_paths' => [
            __DIR__ . '/autoload/{,*.}{global,local}.php',
        ],
    ],
];
