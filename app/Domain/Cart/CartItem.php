<?php

declare(strict_types=1);

namespace App\Domain\Cart;

final readonly class CartItem
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $priceCents,
        public int $quantity,
        public ?string $description = null,
    ) {
    }

    public function withQuantity(int $quantity): self
    {
        return new self(
            $this->sku,
            $this->name,
            $this->priceCents,
            $quantity,
            $this->description
        );
    }

    public function totalCents(): int
    {
        return $this->priceCents * $this->quantity;
    }
}
