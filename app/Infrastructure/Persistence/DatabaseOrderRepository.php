<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderRepositoryInterface;
use Doctrine\DBAL\Connection;

final readonly class DatabaseOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function find(string $id): ?Order
    {
        /** @var array{id: string, shipping_address: string, total_cents: int, geo_location: string|null, created_at: string}|false $row */
        $row = $this->db->fetchAssociative('SELECT * FROM orders WHERE id = ?', [$id]);

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function get(string $id): Order
    {
        return $this->find($id) ?? throw new OrderNotFoundException($id);
    }

    public function findAll(): array
    {
        /** @var list<array{id: string, shipping_address: string, total_cents: int, geo_location: string|null, created_at: string}> $rows */
        $rows = $this->db->fetchAllAssociative('SELECT * FROM orders ORDER BY created_at DESC');

        return array_map($this->hydrate(...), $rows);
    }

    public function save(Order $order): void
    {
        $this->db->insert('orders', [
            'id' => $order->id,
            'shipping_address' => $order->shippingAddress,
            'total_cents' => $order->totalCents,
            'geo_location' => $order->geoLocation,
            'created_at' => $order->createdAt->format('Y-m-d H:i:s'),
        ]);

        foreach ($order->items as $item) {
            $this->db->insert('order_items', [
                'order_id' => $order->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'price_cents' => $item->priceCents,
                'quantity' => $item->quantity,
            ]);
        }
    }

    /**
     * @param array{id: string, shipping_address: string, total_cents: int, geo_location: string|null, created_at: string} $row
     */
    private function hydrate(array $row): Order
    {
        /** @var array<int, array{sku: string, name: string, price_cents: int, quantity: int}> $itemRows */
        $itemRows = $this->db->fetchAllAssociative(
            'SELECT * FROM order_items WHERE order_id = ?',
            [$row['id']],
        );

        $items = array_values(array_map(fn (array $r): OrderItem => new OrderItem(
            sku: $r['sku'],
            name: $r['name'],
            priceCents: $r['price_cents'],
            quantity: $r['quantity'],
        ), $itemRows));

        return new Order(
            id: (string) $row['id'],
            shippingAddress: (string) $row['shipping_address'],
            items: $items,
            totalCents: (int) $row['total_cents'],
            geoLocation: $row['geo_location'] ?? null,
            createdAt: new \DateTime((string) $row['created_at']),
        );
    }
}
