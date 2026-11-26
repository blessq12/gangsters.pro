<?php

namespace App\Domain\Core\Delivery\Routing\Port;

/**
 * Проверка курьера для маршрутного листа. Delivery Domain не импортирует Staff.
 */
interface DeliveryCourierLookupPort
{
    public function assertActiveCourier(int $employeeId): void;
}
