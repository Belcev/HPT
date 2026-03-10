<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
        __DIR__ . '/legacy/refactored',
        __DIR__ . '/bootstrap',
        __DIR__ . '/docker',
        __DIR__ . '/public',
    ])
    ->withPreparedSets(
        psr12: true,
        common: true,
        cleanCode: true,
    );