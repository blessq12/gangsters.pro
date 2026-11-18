<?php

namespace App\Application\Crm\Presenter;

use App\Domain\Crm\Entity\Client;
use App\Domain\Order\Entity\Order;

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
     * @param  list<Order>  $orders
     * @return array{client: array<string, mixed>, orders: list<array<string, mixed>>}
     */
    public function presentWithOrders(Client $client, array $orders): array
    {
        $orderItems = [];
        foreach ($orders as $order) {
            $orderItems[] = $this->orderSnapshot($order);
        }

        return [
            'client' => $this->present($client),
            'orders' => $orderItems,
        ];
    }

    /**
     * @return array{id: int|null, status: string, total: int, created_at: string}
     */
    public function orderSnapshot(Order $order): array
    {
        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'total' => $order->totalRubles(),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
        ];
    }
}
