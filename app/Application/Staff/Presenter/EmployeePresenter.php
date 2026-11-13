<?php

namespace App\Application\Staff\Presenter;

use App\Domain\Staff\Entity\Employee;

final class EmployeePresenter
{
    /**
     * @return array{id: int, name: string, email: string, phone: ?string, role: string, is_active: bool}
     */
    public function present(Employee $employee): array
    {
        return [
            'id' => $employee->id(),
            'name' => $employee->name(),
            'email' => $employee->email(),
            'phone' => $employee->phone(),
            'role' => $employee->role()->value,
            'is_active' => $employee->isActive(),
        ];
    }
}
