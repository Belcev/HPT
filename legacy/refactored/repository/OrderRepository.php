<?php

declare(strict_types=1);

namespace legacy\refactored\repository;

use legacy\refactored\model\Order;

class OrderRepository
{
    private const string FILE_PATH = 'orders.json';

    public function saveOrder(Order $order): void
    {
        $result = file_put_contents(
            filename: self::FILE_PATH,
            data: $order->toJson() . "\n",
            flags: FILE_APPEND | LOCK_EX,
        );

        if ($result === false) {
            throw new \RuntimeException('Could not save order to file');
        }
    }
}
