<?php

declare(strict_types=1);

namespace Legacy\New\repository;

use Legacy\New\model\Order;

class OrderRepository
{
    private const string FILE_PATH = 'orders.json';


    public function saveOrder(Order $order): void
    {
        $encoded = json_encode($order);
        if ($encoded === false) {
            throw new \RuntimeException('Could not encode order to JSON');
        }

        $result = file_put_contents(self::FILE_PATH, $encoded . "\n", FILE_APPEND | LOCK_EX);
        if ($result === false) {
            throw new \RuntimeException('Could not save order to file');
        }
    }
}
