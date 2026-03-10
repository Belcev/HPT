<?php

declare(strict_types=1);

namespace App\Domain\Cart;

use App\Domain\Exception\CartNotFoundException;

interface CartRepositoryInterface
{
    public function find(string $id): ?Cart;

    /**
     * @throws CartNotFoundException
     */
    public function get(string $id): Cart;

    public function create(): Cart;

    public function save(Cart $cart): void;
}
