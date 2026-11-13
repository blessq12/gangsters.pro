<?php

namespace App\Domain\Staff\Event;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use DateTimeImmutable;

/** Сотрудник нанят. */
final readonly class EmployeeHired
{
    public function __construct(
        private int $employeeId,
        private string $email,
        private StaffRole $role,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromEmployee(Employee $employee, ?DateTimeImmutable $occurredAt = null): self
    {
        if (! $employee->hasId()) {
            throw new \LogicException('Событие EmployeeHired можно построить только после сохранения сотрудника.');
        }

        return new self(
            employeeId: $employee->id(),
            email: $employee->email(),
            role: $employee->role(),
            occurredAt: $occurredAt ?? $employee->createdAt(),
        );
    }

    public function employeeId(): int
    {
        return $this->employeeId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function role(): StaffRole
    {
        return $this->role;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
