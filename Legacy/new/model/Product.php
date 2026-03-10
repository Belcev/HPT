<?php

declare(strict_types=1);

namespace Legacy\New\model;

readonly class Product
{
    public function __construct(
        public string $sku,
        public float  $price,
    ) {

    }
}
