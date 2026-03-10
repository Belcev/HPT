<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\CartService;
use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Exception\CartNotFoundException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartRepositoryInterface&MockObject $cartRepository;

    private ProductRepositoryInterface&MockObject $productRepository;

    private CartService $service;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->service = new CartService($this->cartRepository, $this->productRepository);
    }

    public function testCreateReturnsNewCart(): void
    {
        $cart = new Cart('cart-1', new DateTime());

        $this->cartRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($cart);

        $result = $this->service->create();

        $this->assertSame('cart-1', $result->id);
        $this->assertTrue($result->isEmpty());
    }

    public function testAddItemAddsProductToCart(): void
    {
        $cart = new Cart('cart-1', new DateTime());
        $product = new Product('MOUSE-01', 'Wireless Mouse', 49900, 'Ergonomic');

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->productRepository
            ->expects($this->once())
            ->method('get')->with('MOUSE-01')->willReturn($product);
        $this->cartRepository->expects($this->once())->method('save');

        $result = $this->service->addItem('cart-1', 'MOUSE-01', 2);

        $this->assertCount(1, $result->items());
        $this->assertSame('MOUSE-01', $result->items()[0]->sku);
        $this->assertSame(2, $result->items()[0]->quantity);
        $this->assertSame(99800, $result->totalCents());
    }

    public function testAddItemMergesQuantityForDuplicateSku(): void
    {
        $existing = new CartItem('MOUSE-01', 'Wireless Mouse', 49900, 3);
        $cart = new Cart('cart-1', new DateTime(), [$existing]);
        $product = new Product('MOUSE-01', 'Wireless Mouse', 49900);

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->productRepository
            ->expects($this->once())
            ->method('get')->willReturn($product);
        $this->cartRepository->expects($this->once())->method('save');

        $result = $this->service->addItem('cart-1', 'MOUSE-01', 2);

        $this->assertCount(1, $result->items());
        $this->assertSame(5, $result->items()[0]->quantity); // 3 + 2
    }

    public function testAddItemThrowsWhenCartNotFound(): void
    {
        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willThrowException(new CartNotFoundException('bad-id'));

        $this->expectException(CartNotFoundException::class);
        $this->service->addItem('bad-id', 'MOUSE-01', 1);
    }

    public function testAddItemThrowsWhenProductNotFound(): void
    {
        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn(new Cart('cart-1', new DateTime()));
        $this->productRepository
            ->expects($this->once())
            ->method('get')->willThrowException(new ProductNotFoundException('BAD-SKU'));

        $this->expectException(ProductNotFoundException::class);
        $this->service->addItem('cart-1', 'BAD-SKU', 1);
    }

    public function testRemoveItemRemovesEntireLine(): void
    {
        $item = new CartItem('MOUSE-01', 'Wireless Mouse', 49900, 3);
        $cart = new Cart('cart-1', new DateTime(), [$item]);

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->cartRepository->expects($this->once())->method('save');

        $result = $this->service->removeItem('cart-1', 'MOUSE-01');

        $this->assertTrue($result->isEmpty());
    }

    public function testRemoveItemDecreasesQuantityWhenPartialRemove(): void
    {
        $item = new CartItem('MOUSE-01', 'Wireless Mouse', 49900, 5);
        $cart = new Cart('cart-1', new DateTime(), [$item]);

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);

        $result = $this->service->removeItem('cart-1', 'MOUSE-01', 2);

        $this->assertCount(1, $result->items());
        $this->assertSame(3, $result->items()[0]->quantity); // 5 - 2
    }

    public function testRemoveItemSilentlyIgnoresMissingSku(): void
    {
        $cart = new Cart('cart-1', new DateTime());

        $this->cartRepository
            ->expects($this->once())
            ->method('get')->willReturn($cart);
        $this->cartRepository->expects($this->once())->method('save');

        $result = $this->service->removeItem('cart-1', 'NONEXISTENT');

        $this->assertTrue($result->isEmpty());
    }
}
