<?php

namespace App\Domain\Generic\Staff\Repository;

use App\Domain\Generic\Staff\Entity\Employee;
use App\Domain\Generic\Staff\Enum\StaffRole;

interface EmployeeRepository
{
    public function save(Employee $employee): void;

    public function findById(int $id): ?Employee;

    public function findByEmail(string $email): ?Employee;

    /**
     * @return list<Employee>
     */
    public function list(?StaffRole $role = null, ?bool $isActive = null): array;

    public function delete(int $id): void;
}
