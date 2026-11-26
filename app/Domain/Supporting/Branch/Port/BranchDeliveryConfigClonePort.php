<?php

namespace App\Domain\Supporting\Branch\Port;

/**
 * Клонирование тарифной конфигурации доставки. SoT — Branch.
 *
 * @phpstan-type DeliveryCloneSnapshot array{
 *     min_order_amount_kopecks: int|null,
 *     average_delivery_time_minutes: int|null,
 *     zones: list<array{id: string, name: string, fee_rubles: int, is_remote: bool, geometry: array<string, mixed>}>
 * }
 */
interface BranchDeliveryConfigClonePort
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
