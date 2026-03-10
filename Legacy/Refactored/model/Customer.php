<?php

declare(strict_types=1);

namespace Legacy\Refactored\model;

readonly class Customer
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $address,
    ) {
    }
}
