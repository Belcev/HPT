<?php

declare(strict_types=1);

namespace Legacy\New\model;

readonly class OrderLine
{
    public float $totalPrice;

    public function __construct(
        public string $sku,
        public float  $price = 0.0,
        public int    $quantity = 0
    ) {
        $this->totalPrice = $price * $quantity;
    }
}
