<?php

declare(strict_types=1);

namespace App\Domain\Order;

readonly class OrderItem
{
    public int $totalCents;

    public function __construct(
        public string $sku,
        public string $name,
        public int $priceCents,
        public int $quantity,
    ) {
        $this->totalCents = $this->priceCents * $this->quantity;
    }
}
