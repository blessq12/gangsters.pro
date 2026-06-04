<?php

namespace App\Filament\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Client\Model\UR_ClientAddress;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;

final class AdminClientEditReadHelper
{
    /**
     * @return array{client: array<string, mixed>, orders: array{items: list<array<string, mixed>>, total: int}}
     */
    public function payload(int $clientId): array
    {
        $client = UR_Client::query()
            ->with(['addresses'])
            ->find($clientId);

        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        $ordersQuery = ORD_Order::query()
            ->where('client_id', $clientId)
            ->orderByDesc('created_at');

        $total = (int) $ordersQuery->count();
        $orderModels = $ordersQuery->limit(20)->get();

        $items = $orderModels->map(function (ORD_Order $order): array {
            $status = (string) $order->status;

            return [
                'id' => (string) $order->id,
                'status' => $status,
                'status_label' => OrderStatusLabels::statusLabel($status),
                'total' => Money::kopecksToApiRubles((int) $order->total),
                'created_at' => $order->created_at?->toIso8601String() ?? '',
            ];
        })->values()->all();

        return [
            'client' => [
                'id' => (int) $client->id,
                'name' => $client->name,
                'phone' => (string) ($client->phone ?? ''),
                'email' => $client->email,
                'status' => (string) ($client->status ?? ''),
                'birth_date' => $client->birth_date?->format('Y-m-d'),
                'consent_personal_data' => (bool) $client->consent_personal_data,
                'consent_marketing' => (bool) $client->consent_marketing,
                'addresses' => $client->addresses->map(
                    static fn (UR_ClientAddress $address): array => [
                        'id' => (int) $address->id,
                        'type' => (string) ($address->type ?? ''),
                        'title' => (string) ($address->title ?? ''),
                        'street' => (string) ($address->street ?? ''),
                        'house' => (string) ($address->house ?? ''),
                        'entrance' => (string) ($address->entrance ?? ''),
                        'apartment' => (string) ($address->apartment ?? ''),
                    ],
                )->values()->all(),
                'created_at' => $client->created_at?->toIso8601String() ?? '',
            ],
            'orders' => [
                'items' => $items,
                'total' => $total,
            ],
        ];
    }
}
