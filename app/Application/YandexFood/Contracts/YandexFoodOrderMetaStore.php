<?php

namespace App\Application\YandexFood\Contracts;

interface YandexFoodOrderMetaStore
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function upsert(string $orderId, array $meta): void;

    /**
     * @return array<string, mixed>|null
     */
    public function findByOrderId(string $orderId): ?array;

    public function deleteByOrderId(string $orderId): void;
}
