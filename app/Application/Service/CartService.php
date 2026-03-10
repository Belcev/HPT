<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\Cart\CartRepositoryInterface;
use App\Domain\Product\ProductRepositoryInterface;

final readonly class CartService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function create(): Cart
    {
        return $this->cartRepository->create();
    }

    public function get(string $id): Cart
    {
        return $this->cartRepository->get($id);
    }

    public function addItem(string $cartId, string $sku, int $quantity): Cart
    {
        $cart = $this->cartRepository->get($cartId);
        $product = $this->productRepository->get($sku);

        $cart->addItem(new CartItem(
            sku: $product->sku,
            name: $product->name,
            priceCents: $product->priceCents,
            quantity: $quantity,
            description: $product->description,
        ));

        $this->cartRepository->save($cart);

        return $cart;
    }

    public function removeItem(string $cartId, string $sku, ?int $quantity = null): Cart
    {
        $cart = $this->cartRepository->get($cartId);
        $cart->removeItem($sku, $quantity);
        $this->cartRepository->save($cart);

        return $cart;
    }
}
