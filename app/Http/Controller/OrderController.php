<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\Service\OrderService;
use App\Domain\Exception\CartEmptyException;
use App\Domain\Exception\CartNotFoundException;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Order\Order;
use App\Domain\Order\OrderItem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OrderController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var array{cart_id: ?string, shipping_address: ?string} $body */
        $body = $request->getParsedBody();

        if (! isset($body['cart_id'], $body['shipping_address'])) {
            return $this->error($response, 400, 'Missing required fields: cart_id, shipping_address');
        }

        try {
            $order = $this->orderService->create(
                $body['cart_id'],
                $body['shipping_address']
            );
        } catch (CartNotFoundException | CartEmptyException $e) {
            return $this->error($response, 400, $e->getMessage());
        }

        return $this->json($response, $this->orderToArray($order));
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $orders = $this->orderService->getAll();

        return $this->json($response, [
            'orders' => array_map($this->orderToArray(...), $orders),
        ]);
    }

    /**
     * @param array<string, string> $args
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $order = $this->orderService->get($args['id']);
        } catch (OrderNotFoundException $e) {
            return $this->error($response, 404, $e->getMessage());
        }

        return $this->json($response, $this->orderToArray($order));
    }

    /**
     * @return array<string, mixed>
     */
    private function orderToArray(Order $order): array
    {
        return [
            'id' => $order->id,
            'created_at' => $order->createdAt->format(\DateTimeInterface::ATOM),
            'items' => array_map(fn (OrderItem $item): array => [
                'sku' => $item->sku,
                'name' => $item->name,
                'price' => $item->priceCents / 100,
                'quantity' => $item->quantity,
                'total' => $item->totalCents / 100,
            ], $order->items),
            'total' => $order->totalCents / 100,
            'shipping_address' => $order->shippingAddress,
            'geo_location' => $order->geoLocation,
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
