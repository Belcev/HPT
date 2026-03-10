<?php

declare(strict_types=1);

namespace legacy\refactored\model;

readonly class Product
{
    public function __construct(
        public string $sku,
        public int $priceCents,
    ) {
    }
}
