<?php

declare(strict_types=1);

namespace App\Domain\Order;

readonly class Order
{
    /**
     * @param list<OrderItem> $items
     */
    public function __construct(
        public string $id,
        public string $shippingAddress,
        public array $items,
        public int $totalCents,
        public ?string $geoLocation,
        public \DateTime $createdAt,
    ) {
    }
}
