<?php

namespace App\Filament\Order\Support;

use App\Filament\Support\ClientSnapshotLabel;
use App\Infrastructure\Order\Model\ORD_Order;

final class OrderSnapshotReader
{
    /**
     * @return array<string, mixed>
     */
    public static function formDataFromRecord(ORD_Order $record): array
    {
        $cart = self::asArray($record->cart_snapshot);
        $client = self::asArray($record->client_snapshot);
        $delivery = self::asArray($record->delivery_snapshot);
        $payment = self::asArray($record->payment_snapshot);
        $lines = is_array($cart['lines'] ?? null) ? $cart['lines'] : [];

        $address = is_array($delivery['address'] ?? null) ? $delivery['address'] : [];

        return [
            'id' => (string) $record->id,
            'checkout_id' => (string) $record->checkout_id,
            'status' => self::statusLabel((string) $record->status),
            'created_at' => $record->created_at?->format('d.m.Y H:i') ?? '—',
            'cart_lines' => self::mapCartLines($lines),
            'cart_items_total' => self::formatRubles((int) $record->total_rubles),
            'client_kind' => self::clientKindLabel((string) ($client['kind'] ?? '')),
            'client_id' => isset($client['client_id']) ? (string) $client['client_id'] : '—',
            'client_name' => self::clientName($client),
            'client_phone' => ClientSnapshotLabel::formatPhone(
                isset($client['phone']) ? (string) $client['phone'] : null,
            ),
            'client_email' => (string) ($client['email'] ?? '—'),
            'delivery_method' => self::deliveryMethodLabel((string) ($delivery['method'] ?? '')),
            'delivery_street' => (string) ($address['street'] ?? '—'),
            'delivery_house' => (string) ($address['house'] ?? '—'),
            'delivery_entrance' => (string) ($address['entrance'] ?? '—'),
            'delivery_apartment' => (string) ($address['apartment'] ?? '—'),
            'delivery_comment' => (string) ($delivery['comment'] ?? '—'),
            'delivery_scheduled_at' => (string) ($delivery['scheduled_at'] ?? '—'),
            'payment_method' => self::paymentMethodLabel((string) ($payment['method'] ?? '')),
            'payment_change_from' => isset($payment['change_from_rubles'])
                ? self::formatRubles((int) $payment['change_from_rubles'])
                : '—',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'new' => 'Новый',
            'preparing' => 'Готовится',
            'in_transit' => 'В доставке',
            'delivered' => 'Доставлен',
            default => $status !== '' ? $status : '—',
        };
    }

    public static function clientKindLabel(string $kind): string
    {
        return match ($kind) {
            'guest' => 'Гость',
            'registered' => 'Авторизованный клиент',
            default => $kind !== '' ? $kind : '—',
        };
    }

    public static function deliveryMethodLabel(string $method): string
    {
        return match ($method) {
            'courier' => 'Курьер',
            'pickup' => 'Самовывоз',
            default => $method !== '' ? $method : '—',
        };
    }

    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Наличными',
            'card_courier' => 'Картой курьеру',
            'card_online' => 'Картой онлайн',
            default => $method !== '' ? $method : '—',
        };
    }

    /**
     * @param  array<int, mixed>  $lines
     */
    public static function cartTotalRubles(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $total += self::lineTotalRubles($line);
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private static function lineTotalRubles(array $line): int
    {
        if (isset($line['line_total_rubles'])) {
            return (int) $line['line_total_rubles'];
        }

        return (int) ($line['unit_price_rubles'] ?? 0) * (int) ($line['quantity'] ?? 0);
    }

    /**
     * @param  array<int, mixed>  $lines
     * @return list<array<string, string>>
     */
    private static function mapCartLines(array $lines): array
    {
        $mapped = [];

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $mapped[] = [
                'product_id' => (string) ($line['product_id'] ?? '—'),
                'product_name' => (string) ($line['product_name'] ?? '—'),
                'quantity' => (string) ($line['quantity'] ?? '—'),
                'unit_price_rubles' => self::formatRubles((int) ($line['unit_price_rubles'] ?? 0)),
                'line_total_rubles' => self::formatRubles(self::lineTotalRubles($line)),
            ];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private static function asArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private static function formatRubles(int $amount): string
    {
        return number_format($amount, 0, ',', ' ').' ₽';
    }

    /**
     * @param  array<string, mixed>  $client
     */
    private static function clientName(array $client): string
    {
        $name = trim((string) ($client['name'] ?? ''));

        if ($name !== '') {
            return $name;
        }

        $clientId = isset($client['client_id']) ? (int) $client['client_id'] : null;

        if ($clientId === null) {
            return '—';
        }

        $label = ClientSnapshotLabel::forList($client, $clientId);

        return str_contains($label, ' · ')
            ? explode(' · ', $label, 2)[0]
            : $label;
    }
}
