<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
//        __DIR__ . '/src',
//        __DIR__ . '/app',
        __DIR__ . '/Legacy/Refactored',
    ])
    ->withPreparedSets(
        psr12: true,
        common: true,
        cleanCode: true,
    );