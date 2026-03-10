<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class ProductNotFoundException extends \RuntimeException
{
    public function __construct(string $sku)
    {
        parent::__construct("Product '{$sku}' not found");
    }
}
