<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class OrderNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Order '{$id}' not found");
    }
}
