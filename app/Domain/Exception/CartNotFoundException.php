<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class CartNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Cart '{$id}' not found");
    }
}
