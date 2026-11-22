<?php

namespace Tests\Application\Core\Support;

use App\Domain\Generic\Staff\Entity\Employee;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Domain\Generic\Staff\Repository\EmployeeRepository;

final class InMemoryEmployeeRepository implements EmployeeRepository
{
    /** @var array<int, Employee> */
    private array $employees = [];

    public function save(Employee $employee): void
    {
        $this->employees[$employee->id()] = $employee;
    }

    public function findById(int $id): ?Employee
    {
        return $this->employees[$id] ?? null;
    }

    public function findByEmail(string $email): ?Employee
    {
        foreach ($this->employees as $employee) {
            if ($employee->email() === $email) {
                return $employee;
            }
        }

        return null;
    }

    public function list(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): array
    {
        return array_values(array_filter(
            $this->employees,
            static function (Employee $employee) use ($role, $isActive, $branchId): bool {
                if ($role !== null && $employee->role() !== $role) {
                    return false;
                }
                if ($isActive !== null && $employee->isActive() !== $isActive) {
                    return false;
                }
                if ($branchId !== null && $employee->branchId() !== $branchId) {
                    return false;
                }

                return true;
            },
        ));
    }

    public function listPage(
        int $offset,
        int $limit,
        ?StaffRole $role = null,
        ?bool $isActive = null,
        ?int $branchId = null,
    ): array {
        return array_slice($this->list($role, $isActive, $branchId), $offset, $limit);
    }

    public function count(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): int
    {
        return count($this->list($role, $isActive, $branchId));
    }
}
