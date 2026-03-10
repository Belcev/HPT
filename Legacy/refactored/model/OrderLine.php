<?php

declare(strict_types=1);

namespace Legacy\Refactored\model;

readonly class OrderLine
{
    public int $totalPrice;

    public function __construct(
        public string $sku,
        public int $priceCents,
        public int $quantity,
    ) {
        $this->totalPrice = $priceCents * $quantity;
    }
}
