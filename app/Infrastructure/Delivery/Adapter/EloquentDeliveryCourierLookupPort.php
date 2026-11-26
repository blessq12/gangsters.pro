<?php

namespace App\Infrastructure\Delivery\Adapter;

use App\Domain\Core\Delivery\Routing\Port\DeliveryCourierLookupPort;
use App\Domain\Supporting\Staff\Enum\StaffRole;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;

final class EloquentDeliveryCourierLookupPort implements DeliveryCourierLookupPort
{
    public function __construct(
        private readonly EmployeeRepository $employees,
    ) {}

    public function assertActiveCourier(int $employeeId): void
    {
        $courier = $this->employees->findById($employeeId);
        if ($courier === null) {
            throw new \InvalidArgumentException('Курьер не найден.');
        }
        if ($courier->role() !== StaffRole::Courier) {
            throw new \InvalidArgumentException('Сотрудник не является курьером.');
        }
        if (! $courier->isActive()) {
            throw new \InvalidArgumentException('Курьер деактивирован.');
        }
    }
}
