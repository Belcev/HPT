<?php

declare(strict_types=1);

namespace Legacy\Refactored\repository;

use Legacy\Refactored\model\Product;

class ProductRepository
{
    private const string FILE_PATH = 'products.json';

    /**
     * @var array<string, Product>
     */
    private array $products;

    public function __construct()
    {
        $this->products = $this->loadProducts();
    }

    public function findBySku(string $sku): Product|null
    {
        return $this->products[$sku] ?? null;
    }

    /**
     * @return array<string, Product>
     */
    private function loadProducts(): array
    {
        if (! file_exists(self::FILE_PATH)) {
            throw new \RuntimeException('Products file not found');
        }
        $file = file_get_contents(self::FILE_PATH);
        if ($file === false) {
            throw new \RuntimeException('Could not load products from file');
        }
        $loadedProducts = json_decode($file, true);
        if (! is_array($loadedProducts)) {
            return [];
        }
        $products = [];

        foreach ($loadedProducts as $product) {
            if (
                ! is_array($product)
                || ! isset($product['sku'], $product['price'])
                || ! is_string($product['sku'])
                || ! is_int($product['price'])
            ) {
                continue;
            }
            $products[$product['sku']] = new Product(
                sku: $product['sku'],
                price: $product['price'],
            );
        }
        return $products;
    }
}
