<?php

namespace App\Infrastructure\YandexFood\OrderMeta;

use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Infrastructure\YandexFood\Model\YandexFoodOrderMeta;

final class EloquentYandexFoodOrderMetaStore implements YandexFoodOrderMetaStore
{
    public function upsert(string $orderId, array $meta): void
    {
        YandexFoodOrderMeta::query()->updateOrCreate(
            ['order_id' => $orderId],
            ['payload' => $meta],
        );
    }

    public function findByOrderId(string $orderId): ?array
    {
        $record = YandexFoodOrderMeta::query()
            ->where('order_id', $orderId)
            ->first();

        if ($record === null) {
            return null;
        }

        return is_array($record->payload) ? $record->payload : null;
    }

    public function deleteByOrderId(string $orderId): void
    {
        YandexFoodOrderMeta::query()
            ->where('order_id', $orderId)
            ->delete();
    }
}
