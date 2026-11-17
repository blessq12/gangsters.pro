<?php

namespace App\Domain\Delivery\Event;

use DateTimeImmutable;

/** Маршрутный лист назначен; Order переводит заказы в доставку. */
final readonly class RouteSheetAssigned
{
    /**
     * @param  list<int>  $orderIds
     */
    public function __construct(
        private int $routeSheetId,
        private array $orderIds,
        private ?int $courierEmployeeId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function routeSheetId(): int
    {
        return $this->routeSheetId;
    }

    /**
     * @return list<int>
     */
    public function orderIds(): array
    {
        return $this->orderIds;
    }

    public function courierEmployeeId(): ?int
    {
        return $this->courierEmployeeId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
