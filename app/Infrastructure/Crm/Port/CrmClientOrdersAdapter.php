<?php

namespace App\Infrastructure\Crm\Port;

use App\Domain\Crm\Port\CrmClientOrdersPort;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;

final class CrmClientOrdersAdapter implements CrmClientOrdersPort
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function listByClientId(int $clientId): array
    {
        return array_map(
            fn (Order $order): array => $this->mapOrder($order),
            $this->orders->listByClientId($clientId),
        );
    }

    public function findByIdForClient(int $orderId, int $clientId): ?array
    {
        $order = $this->orders->findById($orderId);

        if (! $order instanceof Order || $order->clientId() !== $clientId) {
            return null;
        }

        return $this->mapOrder($order);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOrder(Order $order): array
    {
        $delivery = $order->delivery();
        $payment = $order->payment();

        return [
            'id' => $order->id(),
            'source' => $order->source(),
            'status' => $order->status(),
            'total_rubles' => $order->totalRubles(),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'delivery_method' => is_string($delivery['method'] ?? null)
                ? (string) $delivery['method']
                : null,
            'payment_method' => (string) ($payment['method'] ?? 'cash'),
            'lines' => $this->mapLines($order),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapLines(Order $order): array
    {
        $lines = [];

        foreach ($order->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $payload = is_array($line['payload'] ?? null) ? $line['payload'] : [];
            $kind = is_string($payload['kind'] ?? null) && $payload['kind'] !== ''
                ? (string) $payload['kind']
                : 'user';

            $lines[] = [
                'product_id' => (int) ($line['product_id'] ?? 0),
                'product_name' => (string) ($line['product_name'] ?? ''),
                'quantity' => (int) ($line['quantity'] ?? 0),
                'unit_price_rubles' => (int) ($line['unit_price_rubles'] ?? 0),
                'line_total_rubles' => (int) ($line['line_total_rubles'] ?? 0),
                'kind' => $kind,
            ];
        }

        return $lines;
    }
}
