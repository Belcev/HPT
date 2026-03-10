<?php

declare(strict_types=1);

use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Order\OrderRepositoryInterface;
use App\Domain\Product\ProductRepositoryInterface;
use App\Infrastructure\ExternalApi\GeocoderInterface;
use App\Infrastructure\ExternalApi\NominatimGeocoder;
use App\Infrastructure\Persistence\DatabaseCartRepository;
use App\Infrastructure\Persistence\DatabaseOrderRepository;
use App\Infrastructure\Persistence\DatabaseProductRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;

return [
    Connection::class => fn (): Connection => DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => getenv('DB_HOST') ?: 'mysql',
        'dbname' => getenv('DB_NAME') ?: 'HPT',
        'user' => getenv('DB_USER') ?: 'HPT',
        'password' => getenv('DB_PASS') ?: 'secret',
        'charset' => 'utf8mb4',
    ]),

    Client::class => fn (): Client => new Client([
        'timeout' => 5.0,
    ]),

    CartRepositoryInterface::class => \DI\autowire(DatabaseCartRepository::class),
    OrderRepositoryInterface::class => \DI\autowire(DatabaseOrderRepository::class),
    ProductRepositoryInterface::class => \DI\autowire(DatabaseProductRepository::class),
    GeocoderInterface::class => \DI\autowire(NominatimGeocoder::class),
];
