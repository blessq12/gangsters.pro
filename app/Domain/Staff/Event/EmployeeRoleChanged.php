<?php

namespace App\Domain\Staff\Event;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use DateTimeImmutable;

/** Роль сотрудника изменена. */
final readonly class EmployeeRoleChanged
{
    public function __construct(
        private int $employeeId,
        private StaffRole $from,
        private StaffRole $to,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromEmployee(
        Employee $employee,
        StaffRole $from,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        if (! $employee->hasId()) {
            throw new \LogicException('Событие EmployeeRoleChanged можно построить только для сохранённого сотрудника.');
        }

        return new self(
            employeeId: $employee->id(),
            from: $from,
            to: $employee->role(),
            occurredAt: $occurredAt ?? new DateTimeImmutable(),
        );
    }

    public function employeeId(): int
    {
        return $this->employeeId;
    }

    public function from(): StaffRole
    {
        return $this->from;
    }

    public function to(): StaffRole
    {
        return $this->to;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
