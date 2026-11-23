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
     * @return list<array<string, mixed>>
     */
    public function presentList(array $clients): array
    {
        $result = [];
        foreach ($clients as $client) {
            $result[] = $this->present($client);
        }

        return $result;
    }

    /**
     * @param  list<array{
     *     id: int,
     *     number: string|null,
     *     status: string,
     *     created_at: string,
     *     grand_total_kopecks: int
     * }>  $orders
     * @return array{client: array<string, mixed>, orders: list<array<string, mixed>>}
     */
    public function presentWithOrders(Client $client, array $orders): array
    {
        $orderItems = [];
        foreach ($orders as $order) {
            $orderItems[] = [
                'id' => $order['id'],
                'number' => $order['number'],
                'status' => $order['status'],
                'total' => intdiv((int) $order['grand_total_kopecks'], 100),
                'created_at' => $order['created_at'],
            ];
        }

        return [
            'client' => $this->present($client),
            'orders' => $orderItems,
        ];
    }
}
