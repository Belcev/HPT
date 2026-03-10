<?php

declare(strict_types=1);

namespace Legacy\New\repository;

use Legacy\New\model\Customer;

class CustomerRepository
{
    private const string FILE_PATH = 'customers.json';

    /**
     * @var array<string, Customer>
     */
    private array $customers;

    public function __construct()
    {
        $this->customers = $this->loadCustomers();
    }

    public function findByEmail(string $email): ?Customer
    {
        return $this->customers[$email] ?? null;
    }

    /**
     * @param array{
     *     email: string,
     *     name: string,
     *     address: string
     * } $orderData
     */
    public function createFromArray(array $orderData): Customer
    {
        $customer = new Customer(
            id: $this->generateId(),
            name: $orderData['name'],
            email: $orderData['email'],
            address: $orderData['address']
        );
        $this->save($customer);
        return $customer;
    }

    private function generateId(): int
    {
        if ($this->customers === []) {
            return 1;
        }
        return max(array_map(fn(Customer $c): int => $c->id, $this->customers)) + 1;
    }

    public function save(Customer $customer): void
    {
        $this->customers[$customer->email] = $customer;

        $data = array_map(fn(Customer $c): array => [
            'id'      => $c->id,
            'name'    => $c->name,
            'email'   => $c->email,
            'address' => $c->address,
        ], $this->customers);

        $encoded = json_encode(array_values($data));
        if ($encoded === false) {
            throw new \RuntimeException('Could not encode customers to JSON');
        }

        $result = file_put_contents(self::FILE_PATH, $encoded);
        if ($result === false) {
            throw new \RuntimeException('Could not save customers to file');
        }
    }

    /**
     * @return array<string, Customer>
     */
    private function loadCustomers(): array
    {
        if (! file_exists(self::FILE_PATH)) {
            return [];
        }
        $file = file_get_contents(self::FILE_PATH);
        if ($file === false) {
            throw new \RuntimeException('Could not load Customers from file');
        }

        $loadedCustomers = json_decode($file, true);
        if (! is_array($loadedCustomers)) {
            return [];
        }
        $customers = [];
        foreach ($loadedCustomers as $customer) {
            if (
                ! is_array($customer)
                || ! isset($customer['id'], $customer['name'], $customer['email'], $customer['address'])
                || ! is_int($customer['id'])
                || ! is_string($customer['name'])
                || ! is_string($customer['email'])
                || ! is_string($customer['address'])
            ) {
                continue;
            }
            $customers[$customer['email']] = new Customer(
                id: $customer['id'],
                name: $customer['name'],
                email: $customer['email'],
                address: $customer['address']
            );
        }
        return $customers;
    }
}
