<?php

declare(strict_types=1);

namespace App\Domain\Product;

readonly class Product
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $priceCents,
        public ?string $description = null,
    ) {
    }
}
