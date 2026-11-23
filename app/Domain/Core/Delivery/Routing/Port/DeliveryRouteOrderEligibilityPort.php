<?php

namespace App\Domain\Core\Delivery\Routing\Port;

/**
 * Проверка кандидатов в маршрутный лист без загрузки Order-агрегата в Delivery Application.
 *
 * @phpstan-type EligibleOrder array{order_id: int, branch_id: int, assignable: bool}
 */
interface DeliveryRouteOrderEligibilityPort
{
    /**
     * @param  list<int>  $orderIds
     * @return list<EligibleOrder>
     */
    public function resolve(array $orderIds): array;
}
