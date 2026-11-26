<?php

namespace App\Application\Client\Presenter;

use App\Domain\Supporting\Client\Entity\Client;

final class AdminClientPresenter
{
    public function __construct(
        private readonly ClientPresenter $clientPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Client $client): array
    {
        $base = $this->clientPresenter->present($client);

        return [
            'id' => $base['id'],
            'name' => $base['name'],
            'phone' => $base['phone'],
            'email' => $base['email'],
            'birth_date' => $base['birth_date'],
            'addresses' => $base['addresses'],
            'favorites' => $base['favorites'],
            'created_at' => $base['created_at'],
        ];
    }

    /**
     * @param  list<Client>  $clients
     * @param  array<int, array{orders_count: int, last_order_at: string|null}>  $orderSummaries
     * @return list<array<string, mixed>>
     */
    public function presentList(array $clients, array $orderSummaries = []): array
    {
        $result = [];
        foreach ($clients as $client) {
            $base = $this->present($client);
            $id = (int) ($base['id'] ?? 0);
            $summary = $orderSummaries[$id] ?? ['orders_count' => 0, 'last_order_at' => null];
            $addresses = is_array($base['addresses'] ?? null) ? $base['addresses'] : [];
            $defaultAddress = null;
            foreach ($addresses as $address) {
                if (! is_array($address)) {
                    continue;
                }
                if (! empty($address['is_default'])) {
                    $defaultAddress = $this->formatAddressLine($address);
                    break;
                }
            }
            if ($defaultAddress === null && $addresses !== []) {
                $first = $addresses[0];
                $defaultAddress = is_array($first) ? $this->formatAddressLine($first) : null;
            }

            $result[] = [
                'id' => $base['id'],
                'name' => $base['name'],
                'phone' => $base['phone'],
                'email' => $base['email'],
                'birth_date' => $base['birth_date'],
                'created_at' => $base['created_at'],
                'addresses_count' => count($addresses),
                'default_address' => $defaultAddress,
                'orders_count' => (int) ($summary['orders_count'] ?? 0),
                'last_order_at' => $summary['last_order_at'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function formatAddressLine(array $address): ?string
    {
        $street = trim((string) ($address['street'] ?? ''));
        $house = trim((string) ($address['house'] ?? ''));
        if ($street === '') {
            return null;
        }

        $line = $house !== '' ? "{$street}, {$house}" : $street;
        $apartment = trim((string) ($address['apartment'] ?? ''));
        if ($apartment !== '') {
            $line .= ", кв. {$apartment}";
        }

        return $line;
    }

    /**
     * @param  list<array{
     *     id: int,
     *     number: string|null,
     *     status: string,
     *     created_at: string,
     *     grand_total_kopecks: int,
     *     lines?: list<array{name: string, quantity: int, line_type: string}>
     * }>  $orders
     * @return array{client: array<string, mixed>, orders: list<array<string, mixed>>}
     */
    public function presentWithOrders(Client $client, array $orders): array
    {
        $orderItems = [];
        foreach ($orders as $order) {
            $lines = [];
            foreach ($order['lines'] ?? [] as $line) {
                $lines[] = [
                    'name' => (string) ($line['name'] ?? ''),
                    'quantity' => (int) ($line['quantity'] ?? 0),
                    'line_type' => (string) ($line['line_type'] ?? 'regular'),
                ];
            }
            $orderItems[] = [
                'id' => $order['id'],
                'number' => $order['number'],
                'status' => $order['status'],
                'total' => intdiv((int) $order['grand_total_kopecks'], 100),
                'created_at' => $order['created_at'],
                'lines' => $lines,
            ];
        }

        return [
            'client' => $this->present($client),
            'orders' => $orderItems,
        ];
    }
}
