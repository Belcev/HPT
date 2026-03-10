<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Cart\CartItem;
use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Exception\CartEmptyException;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use App\Domain\Order\OrderRepositoryInterface;
use App\Infrastructure\ExternalApi\GeocoderInterface;

final readonly class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CartRepositoryInterface $cartRepository,
        private GeocoderInterface $geocoder,
    ) {
    }

    public function create(string $cartId, string $shippingAddress): Order
    {
        $cart = $this->cartRepository->get($cartId);

        if ($cart->isEmpty()) {
            throw new CartEmptyException();
        }

        $items = array_map(
            fn (CartItem $i): OrderItem => new OrderItem($i->sku, $i->name, $i->priceCents, $i->quantity),
            $cart->items(),
        );

        $geoLocation = $this->geocoder->geocode($shippingAddress);

        $order = new Order(
            id: $this->generateUuid(),
            shippingAddress: $shippingAddress,
            items: $items,
            totalCents: $cart->totalCents(),
            geoLocation: $geoLocation,
            createdAt: new \DateTime(),
        );

        $this->orderRepository->save($order);

        return $order;
    }

    public function get(string $id): Order
    {
        return $this->orderRepository->get($id);
    }

    /**
     * @return list<Order>
     */
    public function getAll(): array
    {
        return $this->orderRepository->findAll();
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
