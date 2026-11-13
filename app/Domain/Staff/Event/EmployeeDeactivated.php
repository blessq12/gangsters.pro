<?php

namespace App\Domain\Staff\Event;

use App\Domain\Staff\Entity\Employee;
use DateTimeImmutable;

/** Сотрудник деактивирован. */
final readonly class EmployeeDeactivated
{
    public function __construct(
        private int $employeeId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromEmployee(Employee $employee, ?DateTimeImmutable $occurredAt = null): self
    {
        if (! $employee->hasId()) {
            throw new \LogicException('Событие EmployeeDeactivated можно построить только для сохранённого сотрудника.');
        }

        return new self(
            employeeId: $employee->id(),
            occurredAt: $occurredAt ?? new DateTimeImmutable(),
        );
    }

    public function employeeId(): int
    {
        return $this->employeeId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
