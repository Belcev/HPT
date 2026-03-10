<?php

declare(strict_types=1);

namespace App\Domain\Product;

use App\Domain\Exception\ProductNotFoundException;

interface ProductRepositoryInterface
{
    public function findBySku(string $sku): ?Product;

    /**
     * @throws ProductNotFoundException
     */
    public function get(string $sku): Product;
}
