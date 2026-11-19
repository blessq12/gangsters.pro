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

    public function list(?StaffRole $role = null, ?bool $isActive = null): array
    {
        return array_values($this->employees);
    }

    public function listPage(int $offset, int $limit, ?StaffRole $role = null, ?bool $isActive = null): array
    {
        return array_slice($this->list($role, $isActive), $offset, $limit);
    }

    public function count(?StaffRole $role = null, ?bool $isActive = null): int
    {
        return count($this->list($role, $isActive));
    }

    public function delete(int $id): void
    {
        unset($this->employees[$id]);
    }
}
