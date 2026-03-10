<?php

declare(strict_types=1);

namespace legacy\refactored\model;

use DateTime;

final class Order
{
    /**
     * @var list<OrderLine>
     */
    public private(set) array $items = [];

    public private(set) int $totalPrice = 0;

    public function __construct(
        public readonly int $customerId,
        public readonly DateTime $createdAt = new DateTime(),
    ) {
    }

    public function addItem(OrderLine $item): void
    {
        $this->items[] = $item;
        $this->totalPrice += $item->totalPrice;
    }

    public function toJson(): string
    {
        $encoded = json_encode($this->toArray());
        if ($encoded === false) {
            throw new \RuntimeException('Could not encode order to JSON');
        }
        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'items' => array_map(fn (OrderLine $line): array => [
                'sku' => $line->sku,
                'price' => $line->priceCents,
                'quantity' => $line->quantity,
                'total_price' => $line->totalPrice,
            ], $this->items),
            'total_price' => $this->totalPrice,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
