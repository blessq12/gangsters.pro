<?php

namespace App\Domain\Order\Port;

/**
 * Снимок филиала и кухни для заказа: только чтение, массивы без сущностей Company.
 *
 * Снимок филиала: {id, company_id, name, is_active}
 * Снимок кухни: {id, branch_id, city, street, house, latitude, longitude}
 */
interface OrderCompanyPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findBranchSnapshotById(int $branchId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findKitchenSnapshotByBranchId(int $branchId): ?array;
}
