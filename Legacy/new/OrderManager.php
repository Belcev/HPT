<?php

declare(strict_types=1);

namespace Legacy\New;

use Legacy\New\model\Customer;
use Legacy\New\model\Order;
use Legacy\New\model\OrderLine;
use Legacy\New\model\Product;
use Legacy\New\repository\CustomerRepository;
use Legacy\New\repository\OrderRepository;
use Legacy\New\repository\ProductRepository;

class OrderManager
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly ProductRepository $productRepository,
        private readonly OrderRepository $orderRepository,
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
    public function processOrder(array $orderData): true
    {
        $this->validateOrderData($orderData);
        $customer = $this->customerRepository->findByEmail($orderData['email']);
        if (! $customer instanceof Customer) {
            $customer = $this->customerRepository->createFromArray($orderData);
        }

        $order = new Order($customer->id);

        foreach ($orderData['items'] as $item) {
            $this->validateOrderDataItem($item);
            $product = $this->productRepository->findBySku($item['sku']);
            if (! $product instanceof Product) {
                // Ignore missing products silently
                continue;
            }

            /**
             * pokud bude v orderData['items'] více položek se stejným SKU, tak se nesečtou do jednoho řádku, ale vytvoří se více řádků s daným SKU a množstvím z jednotlivých položek
             * mohu implementovat jejich sčítání, ale pokud je jisté, že se to nestane nebo to není problém, nechal bych to takto, protože kontrola by mohla být zbytečné zpomalení
             */
            $line = new OrderLine(
                sku: $product->sku,
                price: $product->price,
                quantity: $item['quantity']
            );
            $order->addItem($line);
        }

        $this->orderRepository->saveOrder($order);
        $message = "Thank you for your order!\n\nTotal: {$order->totalPrice}\n\nWe will deliver to: {$customer->address}";
        Mailer::send($customer->email, 'Order confirmation', $message);

        return true;
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
