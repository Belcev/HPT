<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\OrderService;
use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Exception\CartEmptyException;
use App\Domain\Exception\CartNotFoundException;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Order\Order;
use App\Domain\Order\OrderRepositoryInterface;
use App\Infrastructure\ExternalApi\GeocoderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderRepositoryInterface&MockObject $orderRepository;

    private CartRepositoryInterface&MockObject $cartRepository;

    private GeocoderInterface&MockObject $geocoder;

    private OrderService $service;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->geocoder = $this->createMock(GeocoderInterface::class);
        $this->service = new OrderService($this->orderRepository, $this->cartRepository, $this->geocoder);
    }

    public function testCreateThrowsWhenCartNotFound(): void
    {
        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willThrowException(new CartNotFoundException('bad-id'));

        $this->expectException(CartNotFoundException::class);
        $this->service->create('bad-id', 'Prague');
    }

    public function testCreateThrowsWhenCartIsEmpty(): void
    {
        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn(new Cart('cart-1', new \DateTime()));

        $this->expectException(CartEmptyException::class);
        $this->service->create('cart-1', 'Prague');
    }

    public function testCreateSavesOrderWithGeoLocation(): void
    {
        $item = new CartItem('MOUSE-01', 'Wireless Mouse', 49900, 2);
        $cart = new Cart('cart-1', new \DateTime(), [$item]);

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->geocoder->expects($this->once())->method('geocode')->with('Prague, Czech Republic')->willReturn('50.0755,14.4378');
        $this->orderRepository->expects($this->once())->method('save');

        $order = $this->service->create('cart-1', 'Prague, Czech Republic');

        $this->assertSame('Prague, Czech Republic', $order->shippingAddress);
        $this->assertSame('50.0755,14.4378', $order->geoLocation);
        $this->assertSame(99800, $order->totalCents);
        $this->assertCount(1, $order->items);
        $this->assertSame('MOUSE-01', $order->items[0]->sku);
        $this->assertSame(2, $order->items[0]->quantity);
        $this->assertSame(99800, $order->items[0]->totalCents);
    }

    public function testCreateWithFailedGeocodingStoresNullGeoLocation(): void
    {
        $item = new CartItem('KB-01', 'Keyboard', 129900, 1);
        $cart = new Cart('cart-1', new \DateTime(), [$item]);

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->geocoder->expects($this->once())->method('geocode')->willReturn(null);
        $this->orderRepository->expects($this->once())->method('save');

        $order = $this->service->create('cart-1', 'Unknown address 999');

        $this->assertNull($order->geoLocation);
        $this->assertSame(129900, $order->totalCents);
    }

    public function testGetReturnsOrder(): void
    {
        $order = new Order('order-1', 'Prague', [], 0, null, new \DateTime());

        $this->orderRepository
            ->expects($this->once())
            ->method('get')->with('order-1')->willReturn($order);

        $result = $this->service->get('order-1');

        $this->assertSame('order-1', $result->id);
    }

    public function testGetThrowsWhenOrderNotFound(): void
    {
        $this->orderRepository
            ->expects($this->once())
            ->method('get')->willThrowException(new OrderNotFoundException('bad-id'));

        $this->expectException(OrderNotFoundException::class);
        $this->service->get('bad-id');
    }

    public function testGetAllReturnsOrderList(): void
    {
        $orders = [
            new Order('order-1', 'Prague', [], 0, null, new \DateTime()),
            new Order('order-2', 'Brno', [], 0, null, new \DateTime()),
        ];

        $this->orderRepository->method('findAll')->willReturn($orders);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }
}
