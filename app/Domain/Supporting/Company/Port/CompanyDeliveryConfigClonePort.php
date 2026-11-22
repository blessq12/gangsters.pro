<?php

namespace App\Domain\Supporting\Company\Port;

/**
 * Клонирование конфигурации доставки. Company не владеет DeliveryConfiguration.
 *
 * @phpstan-type DeliveryCloneSnapshot array{
 *     min_order_amount_kopecks: int|null,
 *     average_delivery_time_minutes: int|null,
 *     zones: list<array{id: string, name: string, fee_rubles: int, is_remote: bool, geometry: array<string, mixed>}>
 * }
 */
interface CompanyDeliveryConfigClonePort
{
    /**
     * @return DeliveryCloneSnapshot|null
     */
    public function export(int $branchId): ?array;

    /**
     * @param  DeliveryCloneSnapshot  $snapshot
     */
    public function import(int $branchId, array $snapshot): void;

    public function existsForBranch(int $branchId): bool;
}
