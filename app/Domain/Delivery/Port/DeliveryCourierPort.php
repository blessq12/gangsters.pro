<?php

namespace App\Domain\Delivery\Port;

/**
 * Поиск и проверка курьера в контексте Staff: только массивы, без сущностей Employee.
 *
 * Снимок курьера: {id, name, phone}
 */
interface DeliveryCourierPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findCourierSnapshotById(int $employeeId): ?array;

    /** Сотрудник существует, активен и имеет роль курьера. */
    public function isActiveCourier(int $employeeId): bool;
}
