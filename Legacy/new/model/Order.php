<?php

declare(strict_types=1);

namespace Legacy\New\model;

class Order
{
    /**
     * @param list<OrderLine> $items
     */
    public function __construct(
        readonly int $customerId,
        private(set) array        $items = [],
        private(set) float        $totalPrice = 0.0,
        readonly \DateTime          $createdAt = new \DateTime(),
    ) {
    }

    public function addItem(OrderLine $item): void
    {
        $this->items[] = $item;
        $this->totalPrice += $item->totalPrice;
    }
}
