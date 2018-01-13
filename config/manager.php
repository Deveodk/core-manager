<?php

/**
 * Config file for the core-manager package.
 */
return [
    'formatter' => \DeveoDK\Core\Manager\Formatters\Formatter::class,

    // What should the transformed data be wrapped in
    'wrap' => 'data',

    // Should includes be wrapped?
    'includes_wrap' => false,
];
