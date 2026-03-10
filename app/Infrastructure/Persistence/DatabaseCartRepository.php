<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Exception\CartNotFoundException;
use DateTime;
use Doctrine\DBAL\Connection;

final readonly class DatabaseCartRepository implements CartRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function find(string $id): ?Cart
    {
        /** @var array{id: string, created_at: string}|false $row */
        $row = $this->db->fetchAssociative('SELECT * FROM carts WHERE id = ?', [$id]);
        if ($row === false) {
            return null;
        }

        /** @var array<int, array{sku: string, name: string, price_cents: int, quantity: int, description: string|null}> $itemRows */
        $itemRows = $this->db->fetchAllAssociative(
            'SELECT * FROM cart_items WHERE cart_id = ?',
            [$id],
        );

        $items = array_values(array_map(fn (array $r): CartItem => new CartItem(
            sku: $r['sku'],
            name: $r['name'],
            priceCents: $r['price_cents'],
            quantity: $r['quantity'],
            description: $r['description'],
        ), $itemRows));

        return new Cart(
            id: $row['id'],
            createdAt: new DateTime($row['created_at']),
            items: $items,
        );
    }

    public function get(string $id): Cart
    {
        return $this->find($id) ?? throw new CartNotFoundException($id);
    }

    public function create(): Cart
    {
        $cart = new Cart(id: $this->generateUuid(), createdAt: new DateTime());

        $this->db->insert('carts', [
            'id' => $cart->id,
            'created_at' => $cart->createdAt->format('Y-m-d H:i:s'),
        ]);

        return $cart;
    }

    public function save(Cart $cart): void
    {
        $this->db->delete('cart_items', [
            'cart_id' => $cart->id,
        ]);

        foreach ($cart->items() as $item) {
            $this->db->insert('cart_items', [
                'cart_id' => $cart->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'description' => $item->description,
                'price_cents' => $item->priceCents,
                'quantity' => $item->quantity,
            ]);
        }
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
