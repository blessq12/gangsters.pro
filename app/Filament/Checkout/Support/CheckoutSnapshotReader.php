<?php

namespace App\Filament\Checkout\Support;

use App\Infrastructure\Checkout\Model\CHK_Checkout;

final class CheckoutSnapshotReader
{
    /**
     * @return array<string, mixed>
     */
    public static function formDataFromRecord(CHK_Checkout $record): array
    {
        $cart = self::asArray($record->cart_snapshot);
        $client = self::asArray($record->client_snapshot);
        $delivery = self::asArray($record->delivery_snapshot);
        $payment = self::asArray($record->payment_snapshot);
        $lines = is_array($cart['lines'] ?? null) ? $cart['lines'] : [];

        $address = is_array($delivery['address'] ?? null) ? $delivery['address'] : [];

        return [
            'id' => (string) $record->id,
            'status' => self::statusLabel((string) $record->status),
            'created_at' => $record->created_at?->format('d.m.Y H:i') ?? '—',
            'confirmed_at' => $record->confirmed_at?->format('d.m.Y H:i') ?? '—',
            'cart_lines' => self::mapCartLines($lines),
            'cart_items_total' => self::formatRubles(self::cartTotalRubles($lines)),
            'client_kind' => self::clientKindLabel((string) ($client['kind'] ?? '')),
            'client_id' => isset($client['client_id']) ? (string) $client['client_id'] : '—',
            'client_name' => (string) ($client['name'] ?? '—'),
            'client_phone' => (string) ($client['phone'] ?? '—'),
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
            'draft' => 'Черновик',
            'confirmed' => 'Подтверждено',
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

            $total += (int) ($line['line_total_rubles'] ?? 0);
        }

        return $total;
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
                'line_total_rubles' => self::formatRubles((int) ($line['line_total_rubles'] ?? 0)),
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
}
