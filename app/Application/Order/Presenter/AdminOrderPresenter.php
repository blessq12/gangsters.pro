<?php

namespace App\Application\Order\Presenter;

use App\Domain\Order\Entity\Order;

final class AdminOrderPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $client = $order->client();
        $delivery = $order->delivery();
        $payment = $order->payment();
        $reason = $delivery['cancel_reason'] ?? null;

        return [
            'id' => $order->id(),
            'source' => $order->source(),
            'checkout_id' => $order->checkoutId(),
            'partner_code' => $order->partnerCode(),
            'external_order_id' => $order->externalOrderId(),
            'status' => $order->status(),
            'total' => $order->totalRubles(),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'kitchen_started_at' => $this->kitchenStartedAt($delivery),
            'client' => [
                'kind' => is_string($client['kind'] ?? null) ? $client['kind'] : 'guest',
                'client_id' => isset($client['client_id']) ? (int) $client['client_id'] : null,
                'name' => (string) ($client['name'] ?? ''),
                'phone' => (string) ($client['phone'] ?? ''),
                'email' => is_string($client['email'] ?? null) && $client['email'] !== ''
                    ? $client['email']
                    : null,
            ],
            'delivery' => [
                'method' => (string) ($delivery['method'] ?? 'courier'),
                'address' => $this->formatAddress($delivery),
                'scheduled_at' => is_string($delivery['scheduled_at'] ?? null) && $delivery['scheduled_at'] !== ''
                    ? $delivery['scheduled_at']
                    : null,
                'persons' => isset($delivery['persons']) ? (int) $delivery['persons'] : null,
                'comment' => is_string($delivery['comment'] ?? null) && $delivery['comment'] !== ''
                    ? $delivery['comment']
                    : null,
                'latitude' => $this->coordinate($delivery, 'latitude'),
                'longitude' => $this->coordinate($delivery, 'longitude'),
            ],
            'payment' => [
                'method' => (string) ($payment['method'] ?? ''),
                'change_from_rubles' => isset($payment['change_from_rubles'])
                    ? (int) $payment['change_from_rubles']
                    : null,
            ],
            'reject_reason' => is_string($reason) && $reason !== '' ? $reason : null,
            'lines' => $this->lines($order),
        ];
    }

    /**
     * @param  array<string, mixed>  $delivery
     */
    private function kitchenStartedAt(array $delivery): ?string
    {
        $startedAt = $delivery['kitchen_started_at'] ?? null;

        return is_string($startedAt) && $startedAt !== '' ? $startedAt : null;
    }

    /**
     * @param  array<string, mixed>  $delivery
     */
    private function formatAddress(array $delivery): string
    {
        $address = $delivery['address'] ?? null;
        if (is_string($address) && trim($address) !== '') {
            return trim($address);
        }

        if (is_array($address)) {
            $parts = [];
            foreach (['street', 'house', 'apartment'] as $key) {
                $value = trim((string) ($address[$key] ?? ''));
                if ($value !== '') {
                    $parts[] = $value;
                }
            }
            if ($parts !== []) {
                return implode(', ', $parts);
            }
        }

        return ($delivery['method'] ?? '') === 'pickup' ? 'Самовывоз' : '';
    }

    /**
     * @param  array<string, mixed>  $delivery
     */
    private function coordinate(array $delivery, string $key): ?float
    {
        $address = $delivery['address'] ?? null;
        $value = is_array($address) ? ($address[$key] ?? null) : null;
        if (! is_numeric($value)) {
            $value = $delivery[$key] ?? null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @return list<array{name: string, qty: int, total: int}>
     */
    private function lines(Order $order): array
    {
        $lines = [];
        foreach ($order->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $lines[] = [
                'name' => (string) ($line['product_name'] ?? ''),
                'qty' => (int) ($line['quantity'] ?? 0),
                'total' => (int) ($line['line_total_rubles'] ?? 0),
            ];
        }

        return $lines;
    }
}
