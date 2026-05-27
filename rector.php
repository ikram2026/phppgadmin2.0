<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/*.php', // Adds your root level files like admin.php
        __DIR__ . '/classes',
        __DIR__ . '/conf',
        __DIR__ . '/help',
        __DIR__ . '/lang',
        __DIR__ . '/libraries',
        __DIR__ . '/plugins',
        __DIR__ . '/tests',
        __DIR__ . '/themes',
    ])
    // This activates all rules up to PHP 8.2
    ->withPhpSets(php82: true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
