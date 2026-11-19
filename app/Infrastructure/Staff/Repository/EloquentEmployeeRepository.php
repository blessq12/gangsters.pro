<?php

namespace App\Infrastructure\Staff\Repository;

use App\Domain\Generic\Staff\Entity\Employee;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Domain\Generic\Staff\Repository\EmployeeRepository;
use App\Infrastructure\Staff\Model\STF_Employee;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentEmployeeRepository implements EmployeeRepository
{
    public function save(Employee $employee): void
    {
        $row = $employee->hasId()
            ? STF_Employee::query()->find($employee->id())
            : new STF_Employee();

        if (! $row instanceof STF_Employee) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $row->name = $employee->name();
        $row->email = $employee->email();
        $row->phone = $employee->phone();
        $row->role = $employee->role()->value;
        $row->is_active = $employee->isActive();
        $row->password_hash = $employee->passwordHash();
        $row->save();

        if (! $employee->hasId()) {
            $employee->assignId((int) $row->id);
        }
    }

    public function findById(int $id): ?Employee
    {
        $row = STF_Employee::query()->find($id);

        return $row instanceof STF_Employee ? $this->map($row) : null;
    }

    public function findByEmail(string $email): ?Employee
    {
        $row = STF_Employee::query()->where('email', trim($email))->first();

        return $row instanceof STF_Employee ? $this->map($row) : null;
    }

    public function list(?StaffRole $role = null, ?bool $isActive = null): array
    {
        /** @var Builder<STF_Employee> $query */
        $query = STF_Employee::query()->orderBy('id');

        if ($role instanceof StaffRole) {
            $query->where('role', $role->value);
        }
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->get()
            ->map(fn (STF_Employee $row): Employee => $this->map($row))
            ->all();
    }

    public function delete(int $id): void
    {
        $row = STF_Employee::query()->find($id);
        if (! $row instanceof STF_Employee) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $row->delete();
    }

    private function map(STF_Employee $row): Employee
    {
        $role = StaffRole::tryFrom((string) $row->role) ?? StaffRole::Administrator;

        return Employee::restore(
            id: (int) $row->id,
            name: (string) $row->name,
            email: (string) $row->email,
            phone: $row->phone !== null && $row->phone !== '' ? (string) $row->phone : null,
            role: $role,
            passwordHash: (string) $row->password_hash,
            isActive: (bool) $row->is_active,
            createdAt: $row->created_at?->toDateTimeImmutable() ?? new DateTimeImmutable(),
        );
    }
}
