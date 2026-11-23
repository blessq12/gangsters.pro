<?php

namespace App\Infrastructure\Staff\Repository;

use App\Domain\Supporting\Staff\Entity\Employee;
use App\Domain\Supporting\Staff\Enum\StaffRole;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;
use App\Infrastructure\Staff\Model\EmployeeModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentEmployeeRepository implements EmployeeRepository
{
    public function save(Employee $employee): void
    {
        $row = $employee->hasId()
            ? EmployeeModel::query()->find($employee->id())
            : new EmployeeModel();

        if (! $row instanceof EmployeeModel) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $row->name = $employee->name();
        $row->email = $employee->email();
        $row->phone = $employee->phone();
        $row->role = $employee->role()->value;
        $row->branch_id = $employee->branchId();
        $row->is_active = $employee->isActive();
        $row->password_hash = $employee->passwordHash();
        $row->save();

        if (! $employee->hasId()) {
            $employee->assignId((int) $row->id);
        }
    }

    public function findById(int $id): ?Employee
    {
        $row = EmployeeModel::query()->find($id);

        return $row instanceof EmployeeModel ? $this->map($row) : null;
    }

    public function findByEmail(string $email): ?Employee
    {
        $row = EmployeeModel::query()->where('email', trim($email))->first();

        return $row instanceof EmployeeModel ? $this->map($row) : null;
    }

    public function list(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): array
    {
        return $this->listPage(0, max(1, $this->count($role, $isActive, $branchId)), $role, $isActive, $branchId);
    }

    public function listPage(
        int $offset,
        int $limit,
        ?StaffRole $role = null,
        ?bool $isActive = null,
        ?int $branchId = null,
    ): array {
        return $this->filteredQuery($role, $isActive, $branchId)
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (EmployeeModel $row): Employee => $this->map($row))
            ->all();
    }

    public function count(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): int
    {
        return (int) $this->filteredQuery($role, $isActive, $branchId)->count();
    }

    /**
     * @return Builder<EmployeeModel>
     */
    private function filteredQuery(
        ?StaffRole $role = null,
        ?bool $isActive = null,
        ?int $branchId = null,
    ): Builder {
        /** @var Builder<EmployeeModel> $query */
        $query = EmployeeModel::query()->orderBy('id');

        if ($role instanceof StaffRole) {
            $query->where('role', $role->value);
        }
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    private function map(EmployeeModel $row): Employee
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
            branchId: $row->branch_id !== null ? (int) $row->branch_id : null,
        );
    }
}
