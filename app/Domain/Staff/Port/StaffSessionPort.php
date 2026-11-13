<?php

namespace App\Domain\Staff\Port;

interface StaffSessionPort
{
    public function login(int $employeeId): void;

    public function logout(): void;

    public function currentEmployeeId(): ?int;
}
