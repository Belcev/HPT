<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\Service\CartService;
use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\Exception\CartNotFoundException;
use App\Domain\Exception\ProductNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CartController
{
    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cart = $this->cartService->create();

        return $this->json($response, $this->cartToArray($cart), 200);
    }

    /**
     * @param array<string, string> $args
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $cart = $this->cartService->get($args['id']);
        } catch (CartNotFoundException $e) {
            return $this->error($response, 404, $e->getMessage());
        }

        return $this->json($response, $this->cartToArray($cart));
    }

    public function addItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var array<string, mixed>|null $body */
        $body = $request->getParsedBody();

        if (! isset($body['cart_id'], $body['sku'])) {
            return $this->error($response, 400, 'Missing required fields: cart_id, sku');
        }

        /** @var array{cart_id: string, sku: string, quantity?: int} $body */
        $quantity = isset($body['quantity']) ? (int) $body['quantity'] : 1;
        if ($quantity < 1) {
            return $this->error($response, 400, 'Quantity must be at least 1');
        }

        try {
            $cart = $this->cartService->addItem((string) $body['cart_id'], (string) $body['sku'], $quantity);
        } catch (CartNotFoundException|ProductNotFoundException $e) {
            return $this->error($response, 404, $e->getMessage());
        }

        return $this->json($response, $this->cartToArray($cart));
    }

    public function removeItem(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var array<string, mixed>|null $body */
        $body = $request->getParsedBody();

        if (! isset($body['cart_id'], $body['sku'])) {
            return $this->error($response, 400, 'Missing required fields: cart_id, sku');
        }

        /** @var array{cart_id: string, sku: string, quantity?: int} $body */
        $quantity = isset($body['quantity']) ? (int) $body['quantity'] : null;

        try {
            $cart = $this->cartService->removeItem((string) $body['cart_id'], (string) $body['sku'], $quantity);
        } catch (CartNotFoundException $e) {
            return $this->error($response, 404, $e->getMessage());
        }

        return $this->json($response, $this->cartToArray($cart));
    }

    /**
     * @return array<string, mixed>
     */
    private function cartToArray(Cart $cart): array
    {
        return [
            'id' => $cart->id,
            'items' => array_map(fn (CartItem $item): array => [
                'product' => [
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'price' => $item->priceCents / 100,
                    'description' => $item->description,
                ],
                'quantity' => $item->quantity,
                'total' => $item->totalCents() / 100,
            ], $cart->items()),
            'item_count' => $cart->itemCount(),
            'total_quantity' => $cart->totalQuantity(),
            'total' => $cart->totalCents() / 100,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function error(ResponseInterface $response, int $status, string $message): ResponseInterface
    {
        return $this->json($response, [
            'error' => $message,
        ], $status);
    }
}
