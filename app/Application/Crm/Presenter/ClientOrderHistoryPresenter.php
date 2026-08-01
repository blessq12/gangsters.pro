<?php

namespace App\Application\Crm\Presenter;

use App\Domain\Order\Entity\Order;

/**
 * Контракт истории заказов для FE (ClientOrderHistory / orderStore).
 */
final class ClientOrderHistoryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $items = [];
        foreach ($order->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $payload = is_array($line['payload'] ?? null) ? $line['payload'] : null;
            $kind = is_string($payload['kind'] ?? null) && $payload['kind'] !== ''
                ? $payload['kind']
                : 'user';

            $items[] = [
                'id' => (int) ($line['product_id'] ?? 0),
                'quantity' => (int) ($line['quantity'] ?? 0),
                'row_total' => (int) ($line['line_total_rubles'] ?? 0),
                'kind' => $kind,
                'product' => [
                    'name' => (string) ($line['product_name'] ?? ''),
                ],
            ];
        }

        $payment = $order->payment();
        $paymentMethod = (string) ($payment['method'] ?? 'cash');
        $delivery = $order->delivery();

        return [
            'id' => $order->id(),
            'source' => $order->source(),
            'status' => $order->status(),
            'total' => $order->totalRubles(),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'delivery' => [
                'method' => is_string($delivery['method'] ?? null)
                    ? (string) $delivery['method']
                    : null,
            ],
            'payment' => [
                'method' => $this->presentPaymentMethodForFrontend($paymentMethod),
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
