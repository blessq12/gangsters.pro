<?php

namespace App\Application\Staff\Presenter;

use App\Domain\Generic\Staff\Entity\Employee;

final class EmployeePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Employee $employee): array
    {
        return [
            'id' => $employee->hasId() ? $employee->id() : null,
            'name' => $employee->name(),
            'email' => $employee->email(),
            'phone' => $employee->phone(),
            'role' => $employee->role()->value,
            'is_active' => $employee->isActive(),
            'created_at' => $employee->createdAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param  list<Employee>  $employees
     * @return list<array<string, mixed>>
     */
    public function presentList(array $employees): array
    {
        $result = [];
        foreach ($employees as $employee) {
            $result[] = $this->present($employee);
        }

        return $result;
    }
}
