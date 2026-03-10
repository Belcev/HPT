<?php

declare(strict_types=1);

namespace App\Domain\Order;

interface OrderRepositoryInterface
{
    public function find(string $id): ?Order;

    /**
     * @throws \App\Domain\Exception\OrderNotFoundException
     */
    public function get(string $id): Order;

    /**
     * @return list<Order>
     */
    public function findAll(): array;

    public function save(Order $order): void;
}
