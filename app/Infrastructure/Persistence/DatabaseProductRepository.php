<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use Doctrine\DBAL\Connection;

final readonly class DatabaseProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Connection $db
    ) {
    }

    public function findBySku(string $sku): ?Product
    {
        /** @var array{sku: string, name: string, price_cents: int, description: string|null}|false $row */
        $row = $this->db->fetchAssociative('SELECT * FROM products WHERE sku = ?', [$sku]);

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function get(string $sku): Product
    {
        return $this->findBySku($sku) ?? throw new ProductNotFoundException($sku);
    }

    /**
     * @param array{sku: string, name: string, price_cents: int, description: string|null} $row
     */
    private function hydrate(array $row): Product
    {
        return new Product(
            sku: (string) $row['sku'],
            name: (string) $row['name'],
            priceCents: (int) $row['price_cents'],
            description: $row['description'] ?? null,
        );
    }
}
