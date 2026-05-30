<?php

namespace App\Support\Shopping;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;

final class AdminCheckoutDraftFormatter
{
    /**
     * @param  array<string, mixed>|null  $draft
     * @return list<array{
     *     title: string,
     *     rows: list<array{label: string, value: string|null}>
     * }>
     */
    public static function sections(?array $draft): array
    {
        if ($draft === null || $draft === []) {
            return [];
        }

        $sections = [];

        $guestContact = $draft['guest_contact'] ?? null;
        if (is_array($guestContact) && $guestContact !== []) {
            $sections[] = [
                'title' => 'Контакт гостя',
                'rows' => self::compactRows([
                    'Имя' => self::stringOrNull($guestContact['name'] ?? null),
                    'Телефон' => self::stringOrNull($guestContact['phone'] ?? null),
                    'Email' => self::stringOrNull($guestContact['email'] ?? null),
                ]),
            ];
        }

        $deliveryInfo = $draft['delivery_info'] ?? null;
        if (is_array($deliveryInfo) && $deliveryInfo !== []) {
            $method = self::stringOrNull($deliveryInfo['method'] ?? null);
            $sections[] = [
                'title' => 'Доставка',
                'rows' => self::compactRows([
                    'Способ' => self::deliveryMethodLabel($method),
                    'Адрес' => OrderStatusLabels::formatAddress(
                        is_array($deliveryInfo['address'] ?? null) ? $deliveryInfo['address'] : null,
                    ),
                    'Комментарий' => self::stringOrNull($deliveryInfo['comment'] ?? null),
                ]),
            ];
        }

        $paymentInfo = $draft['payment_info'] ?? null;
        if (is_array($paymentInfo) && $paymentInfo !== []) {
            $method = self::stringOrNull($paymentInfo['method'] ?? null);
            $changeFrom = $paymentInfo['change_from'] ?? null;
            $sections[] = [
                'title' => 'Оплата',
                'rows' => self::compactRows([
                    'Способ' => self::paymentMethodLabel($method),
                    'Сдача с' => is_numeric($changeFrom)
                        ? sprintf('%.2f ₽', (float) $changeFrom)
                        : self::stringOrNull($changeFrom),
                ]),
            ];
        }

        $promotions = $draft['promotions'] ?? null;
        if (is_array($promotions) && $promotions !== []) {
            $giftProductId = $promotions['free_roll_gift_product_id'] ?? null;
            $sections[] = [
                'title' => 'Акции',
                'rows' => self::compactRows([
                    'Подарок (roll)' => is_numeric($giftProductId)
                        ? '#'.(int) $giftProductId
                        : self::stringOrNull($giftProductId),
                ]),
            ];
        }

        return array_values(array_filter(
            $sections,
            static fn (array $section): bool => $section['rows'] !== [],
        ));
    }

    /**
     * @param  array<string, string|null>  $rows
     * @return list<array{label: string, value: string|null}>
     */
    private static function compactRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $out[] = [
                'label' => (string) $label,
                'value' => $value,
            ];
        }

        return $out;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function deliveryMethodLabel(?string $method): ?string
    {
        if ($method === null) {
            return null;
        }

        $enum = DeliveryMethod::tryFrom($method);

        return $enum?->label() ?? $method;
    }

    private static function paymentMethodLabel(?string $method): ?string
    {
        if ($method === null) {
            return null;
        }

        $enum = PaymentMethod::tryFrom($method);

        return $enum?->label() ?? $method;
    }

    public static function formatRubles(int $kopecks): string
    {
        return number_format(Money::kopecksToApiRubles($kopecks), 2, ',', ' ').' ₽';
    }
}
