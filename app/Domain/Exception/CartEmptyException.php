<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class CartEmptyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot create order from an empty cart');
    }
}
