<?php

namespace App\Application\Order\Presenter;

use App\Domain\Order\Entity\Order;

final class OrderPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $lines = [];
        foreach ($order->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $payload = is_array($line['payload'] ?? null) ? $line['payload'] : null;
            $kind = is_string($payload['kind'] ?? null) && $payload['kind'] !== ''
                ? $payload['kind']
                : 'user';

            $lines[] = [
                'id' => (int) ($line['product_id'] ?? 0),
                'quantity' => (int) ($line['quantity'] ?? 0),
                'row_total' => (int) ($line['line_total_rubles'] ?? 0),
                'kind' => $kind,
                'product' => [
                    'name' => (string) ($line['product_name'] ?? ''),
                ],
            ];
        }

        $itemsTotal = 0;
        foreach ($lines as $line) {
            $itemsTotal += $line['row_total'];
        }

        $payment = $order->payment();
        $paymentMethod = (string) ($payment['method'] ?? 'cash');

        return [
            'id' => $order->id(),
            'source' => $order->source(),
            'client_request_id' => $order->clientRequestId(),
            'checkout_id' => $order->checkoutId(),
            'partner_code' => $order->partnerCode(),
            'external_order_id' => $order->externalOrderId(),
            'status' => $order->status(),
            'total' => $itemsTotal,
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'client' => $order->client(),
            'delivery' => $order->delivery(),
            'payment' => [
                'method' => $this->presentPaymentMethodForFrontend($paymentMethod),
                'change_from_rubles' => isset($payment['change_from_rubles'])
                    ? (int) $payment['change_from_rubles']
                    : null,
            ],
            'items' => $lines,
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
