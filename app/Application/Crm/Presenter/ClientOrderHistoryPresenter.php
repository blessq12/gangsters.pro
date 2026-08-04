<?php

namespace App\Application\Crm\Presenter;

/**
 * Контракт истории заказов для FE (ClientOrderHistory / orderStore).
 */
final class ClientOrderHistoryPresenter
{
    /**
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    public function present(array $order): array
    {
        $items = [];
        foreach ($order['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $items[] = [
                'id' => (int) ($line['product_id'] ?? 0),
                'quantity' => (int) ($line['quantity'] ?? 0),
                'row_total' => (int) ($line['line_total_rubles'] ?? 0),
                'kind' => (string) ($line['kind'] ?? 'user'),
                'product' => [
                    'name' => (string) ($line['product_name'] ?? ''),
                ],
            ];
        }

        return [
            'id' => (int) ($order['id'] ?? 0),
            'source' => $order['source'] ?? null,
            'status' => $order['status'] ?? null,
            'total' => (int) ($order['total_rubles'] ?? 0),
            'created_at' => $order['created_at'] ?? null,
            'delivery' => [
                'method' => is_string($order['delivery_method'] ?? null)
                    ? (string) $order['delivery_method']
                    : null,
            ],
            'payment' => [
                'method' => $this->presentPaymentMethodForFrontend(
                    (string) ($order['payment_method'] ?? 'cash'),
                ),
            ],
            'items' => $items,
        ];
    }

    private function presentPaymentMethodForFrontend(string $method): string
    {
        return match ($method) {
            'card_courier', 'card_online' => 'card',
            default => $method,
        };
    }
}
