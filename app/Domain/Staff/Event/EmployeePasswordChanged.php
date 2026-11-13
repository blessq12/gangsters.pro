<?php

namespace App\Domain\Staff\Event;

use App\Domain\Staff\Entity\Employee;
use DateTimeImmutable;

/** Пароль сотрудника изменён. */
final readonly class EmployeePasswordChanged
{
    public function __construct(
        private int $employeeId,
        private string $email,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromEmployee(Employee $employee, ?DateTimeImmutable $occurredAt = null): self
    {
        if (! $employee->hasId()) {
            throw new \LogicException('Событие EmployeePasswordChanged можно построить только для сохранённого сотрудника.');
        }

        return new self(
            employeeId: $employee->id(),
            email: $employee->email(),
            occurredAt: $occurredAt ?? new DateTimeImmutable(),
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

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
