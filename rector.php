<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
//        __DIR__ . '/src',
//        __DIR__ . '/app',
        __DIR__ . '/Legacy/new',
    ])
    ->withPhpSets(php84: true)
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
    ]);