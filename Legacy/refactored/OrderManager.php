<?php

declare(strict_types=1);

namespace legacy\refactored;

use legacy\refactored\model\Customer;
use legacy\refactored\model\Order;
use legacy\refactored\model\OrderLine;
use legacy\refactored\model\Product;
use legacy\refactored\repository\CustomerRepository;
use legacy\refactored\repository\OrderRepository;
use legacy\refactored\repository\ProductRepository;

final readonly class OrderManager
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private ProductRepository $productRepository,
        private OrderRepository $orderRepository,
        private Mailer $mailer,
    ) {
    }

    /**
     * @param array{
     *     email: string,
     *     name: string,
     *     address: string,
     *     items: array<array{sku: string, quantity: int}>
     * } $orderData
     */
    public function processOrder(array $orderData): void
    {
        $this->validateOrderData($orderData);

        $customer = $this->customerRepository->findByEmail($orderData['email']);
        if (! $customer instanceof Customer) {
            $customer = $this->customerRepository->create(
                name: $orderData['name'],
                email: $orderData['email'],
                address: $orderData['address'],
            );
        }

        $order = new Order($customer->id);

        foreach ($orderData['items'] as $item) {
            $this->validateOrderDataItem($item);
            $product = $this->productRepository->findBySku($item['sku']);
            if (! $product instanceof Product) {
                // Ignore missing products silently
                continue;
            }

            $line = new OrderLine(
                sku: $product->sku,
                priceCents: $product->priceCents,
                quantity: $item['quantity'],
            );
            $order->addItem($line);
        }

        $this->orderRepository->saveOrder($order);

        $message = "Thank you for your order!\n\nTotal: {$order->totalPrice}\n\nWe will deliver to: {$customer->address}";
        $this->mailer->send($customer->email, 'Order confirmation', $message);
    }

    /**
     * @param array<mixed> $orderData
     */
    private function validateOrderData(array $orderData): void
    {
        if (! isset($orderData['email'], $orderData['name'], $orderData['address'], $orderData['items'])) {
            throw new \InvalidArgumentException('Missing required order data');
        }
        if (! is_string($orderData['email']) || ! is_string($orderData['name']) || ! is_string($orderData['address'])) {
            throw new \InvalidArgumentException('Email, Name and Address must be strings');
        }
        if (! is_array($orderData['items'])) {
            throw new \InvalidArgumentException('Items must be an array');
        }
    }

    /**
     * @param array<mixed> $item
     */
    private function validateOrderDataItem(array $item): void
    {
        if (! isset($item['sku'], $item['quantity'])
            || ! is_string($item['sku'])
            || ! is_int($item['quantity'])
        ) {
            throw new \InvalidArgumentException('Invalid item structure');
        }
    }
}
