<?php

declare(strict_types=1);

namespace App\Domain\Cart;

final class Cart
{
    /**
     * @var array<string, CartItem>
     */
    private array $items = [];

    /**
     * @param list<CartItem> $items
     */
    public function __construct(
        public readonly string $id,
        public readonly \DateTime $createdAt,
        array $items = [],
    ) {
        foreach ($items as $item) {
            $this->items[$item->sku] = $item;
        }
    }

    public function addItem(CartItem $item): void
    {
        if (isset($this->items[$item->sku])) {
            $existing = $this->items[$item->sku];
            $this->items[$item->sku] = $existing->withQuantity($existing->quantity + $item->quantity);
        } else {
            $this->items[$item->sku] = $item;
        }
    }

    public function removeItem(string $sku, ?int $quantity = null): void
    {
        if (! isset($this->items[$sku])) {
            return;
        }

        if ($quantity === null) {
            unset($this->items[$sku]);
            return;
        }

        $newQuantity = $this->items[$sku]->quantity - $quantity;
        if ($newQuantity <= 0) {
            unset($this->items[$sku]);
        } else {
            $this->items[$sku] = $this->items[$sku]->withQuantity($newQuantity);
        }
    }

    /**
     * @return list<CartItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function totalCents(): int
    {
        return array_sum(array_map(fn (CartItem $i): int => $i->totalCents(), $this->items));
    }

    public function itemCount(): int
    {
        return count($this->items);
    }

    public function totalQuantity(): int
    {
        return array_sum(array_map(fn (CartItem $i): int => $i->quantity, $this->items));
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
