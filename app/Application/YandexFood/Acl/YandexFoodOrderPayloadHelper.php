<?php

namespace App\Application\YandexFood\Acl;

/**
 * Общая валидация и разбор payload заказа Яндекс.Еды для create/update.
 */
final class YandexFoodOrderPayloadHelper
{
    /**
     * @return array{code: int, description: string}
     */
    public static function failure(string $description): array
    {
        return [
            'code' => 100,
            'description' => $description,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null null если ок
     */
    public static function validateCreateShape(array $data, string $failureDescription): ?array
    {
        $top = ['discriminator', 'eatsId', 'restaurantId', 'deliveryInfo', 'paymentInfo', 'items'];
        foreach ($top as $key) {
            if (!array_key_exists($key, $data)) {
                return self::failure($failureDescription);
            }
        }

        if (!is_array($data['deliveryInfo']) || !is_array($data['paymentInfo']) || !is_array($data['items'])) {
            return self::failure($failureDescription);
        }

        $d = $data['deliveryInfo'];
        if (!isset($d['clientName'], $d['phoneNumber'], $d['deliveryDate'], $d['deliveryAddress']) || !is_array($d['deliveryAddress'])) {
            return self::failure($failureDescription);
        }

        $addr = $d['deliveryAddress'];
        if (!isset($addr['full'], $addr['latitude'], $addr['longitude'])) {
            return self::failure($failureDescription);
        }

        $p = $data['paymentInfo'];
        if (!isset($p['paymentType'], $p['itemsCost'], $p['deliveryFee'], $p['total'], $p['change'])) {
            return self::failure($failureDescription);
        }

        if (!array_key_exists('persons', $data)) {
            return self::failure($failureDescription);
        }

        if ($data['items'] === []) {
            return self::failure($failureDescription);
        }

        foreach ($data['items'] as $item) {
            if (!is_array($item) || !isset($item['id'], $item['quantity'], $item['price'])) {
                return self::failure($failureDescription);
            }
        }

        return null;
    }

    /**
     * Полный апдейт как у создания заказа (все обязательные поля в теле).
     *
     * @param  array<string, mixed>  $p
     */
    public static function isFullYandexUpdate(array $p): bool
    {
        foreach (['discriminator', 'eatsId', 'restaurantId', 'deliveryInfo', 'paymentInfo', 'items', 'persons'] as $k) {
            if (!array_key_exists($k, $p)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function resolveClientId(array $data): ?int
    {
        $raw = $data['client_id'] ?? $data['clientId'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        $id = (int) $raw;

        return $id > 0 ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $paymentInfo
     * @param  array<string, mixed>  $promos
     */
    public static function appendYandexMetaToComment(
        string $comment,
        mixed $eatsId,
        mixed $restaurantId,
        array $paymentInfo,
        mixed $persons,
        array $promos,
    ): string {
        $meta = [
            'yandex_eats_id' => $eatsId,
            'yandex_restaurant_id' => $restaurantId,
            'yandex_persons' => $persons,
            'yandex_payment' => [
                'itemsCost' => $paymentInfo['itemsCost'] ?? null,
                'deliveryFee' => $paymentInfo['deliveryFee'] ?? null,
                'total' => $paymentInfo['total'] ?? null,
                'change' => $paymentInfo['change'] ?? null,
            ],
            'yandex_promos' => $promos,
        ];

        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);
        if ($comment === '') {
            return '[yandex_meta] ' . $metaJson;
        }

        return $comment . "\n\n[yandex_meta] " . $metaJson;
    }
}
