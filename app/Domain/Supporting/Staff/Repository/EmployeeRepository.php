<?php

namespace App\Domain\Supporting\Staff\Repository;

use App\Domain\Supporting\Staff\Entity\Employee;
use App\Domain\Supporting\Staff\Enum\StaffRole;

interface EmployeeRepository
{
    public function save(Employee $employee): void;

    public function findById(int $id): ?Employee;

    public function findByEmail(string $email): ?Employee;

    /**
     * @return list<Employee>
     */
    public function list(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): array;

    /**
     * @return list<Employee>
     */
    public function listPage(
        int $offset,
        int $limit,
        ?StaffRole $role = null,
        ?bool $isActive = null,
        ?int $branchId = null,
    ): array;

    public function count(?StaffRole $role = null, ?bool $isActive = null, ?int $branchId = null): int;
}
