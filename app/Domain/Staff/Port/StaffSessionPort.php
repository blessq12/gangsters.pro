<?php

namespace App\Domain\Staff\Port;

interface StaffSessionPort
{
    /**
     * Issue a Sanctum personal access token for the employee.
     */
    public function login(int $employeeId): string;

    public function logout(): void;

    public function currentEmployeeId(): ?int;
}
