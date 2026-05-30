<?php

namespace App\Filament\Operations\Support;

use App\Filament\Operations\Resources\ClientResource;

final class FilamentActiveCartDetailMapper
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public static function toFormState(array $snapshot): array
    {
        $client = is_array($snapshot['client'] ?? null) ? $snapshot['client'] : [];
        $session = is_array($snapshot['session'] ?? null) ? $snapshot['session'] : [];
        $cart = is_array($snapshot['cart'] ?? null) ? $snapshot['cart'] : [];
        $favorites = is_array($snapshot['favorites'] ?? null) ? $snapshot['favorites'] : [];
        $checkout = is_array($snapshot['checkout'] ?? null) ? $snapshot['checkout'] : [];

        $clientId = isset($client['id']) ? (int) $client['id'] : null;

        $state = [
            'load_error' => false,
            'load_error_message' => null,
            'client_label' => $client['label'] ?? '—',
            'client_type_label' => $client['type_label'] ?? '—',
            'client_type_badge_color' => $client['badge_color'] ?? 'gray',
            'client_id' => $clientId,
            'client_phone' => $client['phone'] ?? null,
            'client_email' => $client['email'] ?? null,
            'client_edit_url' => $clientId !== null
                ? ClientResource::getUrl('edit', ['record' => $clientId])
                : null,
            'session_id' => $session['id'] ?? null,
            'session_public_id' => $session['public_id'] ?? null,
            'session_created_at' => $session['created_at'] ?? null,
            'session_updated_at' => $session['updated_at'] ?? null,
            'session_expires_at' => $session['expires_at'] ?? null,
            'cart_summary' => self::formatCartSummary($cart),
            'cart_lines' => array_values(is_array($cart['lines'] ?? null) ? $cart['lines'] : []),
            'favorite_items' => array_values(is_array($favorites['items'] ?? null) ? $favorites['items'] : []),
            'favorites_count' => (int) ($favorites['count'] ?? 0),
            'has_checkout_draft' => (bool) ($checkout['has_draft'] ?? false),
            'checkout_draft_status' => ($checkout['has_draft'] ?? false) ? 'Начат' : 'Не начат',
            'checkout_guest' => [],
            'checkout_delivery' => [],
            'checkout_payment' => [],
            'checkout_promotions' => [],
        ];

        foreach (is_array($checkout['sections'] ?? null) ? $checkout['sections'] : [] as $section) {
            if (! is_array($section)) {
                continue;
            }

            $key = self::checkoutSectionKey((string) ($section['title'] ?? ''));
            $state[$key] = self::rowsToKeyValue(is_array($section['rows'] ?? null) ? $section['rows'] : []);
        }

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    public static function loadError(string $message): array
    {
        return [
            'load_error' => true,
            'load_error_message' => $message,
            'client_label' => '—',
            'client_type_label' => '—',
            'client_type_badge_color' => 'gray',
            'client_id' => null,
            'client_phone' => null,
            'client_email' => null,
            'client_edit_url' => null,
            'session_id' => null,
            'session_public_id' => null,
            'session_created_at' => null,
            'session_updated_at' => null,
            'session_expires_at' => null,
            'cart_summary' => null,
            'cart_lines' => [],
            'favorite_items' => [],
            'favorites_count' => 0,
            'has_checkout_draft' => false,
            'checkout_draft_status' => 'Не начат',
            'checkout_guest' => [],
            'checkout_delivery' => [],
            'checkout_payment' => [],
            'checkout_promotions' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $cart
     */
    private static function formatCartSummary(array $cart): ?string
    {
        if ((bool) ($cart['is_empty'] ?? true)) {
            return 'Корзина пуста';
        }

        return sprintf(
            '%d поз. · %d шт.',
            (int) ($cart['lines_count'] ?? 0),
            (int) ($cart['total_quantity'] ?? 0),
        );
    }

    private static function checkoutSectionKey(string $title): string
    {
        return match ($title) {
            'Контакт гостя' => 'checkout_guest',
            'Доставка' => 'checkout_delivery',
            'Оплата' => 'checkout_payment',
            'Акции' => 'checkout_promotions',
            default => 'checkout_other',
        };
    }

    /**
     * @param  list<array{label: string, value: string|null}>  $rows
     * @return array<string, string>
     */
    private static function rowsToKeyValue(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }

            $out[$label] = $value;
        }

        return $out;
    }
}
