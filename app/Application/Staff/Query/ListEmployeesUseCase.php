<?php

namespace App\Application\Staff\Query;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Repository\EmployeeRepository;

final class ListEmployeesUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return list<array{id: int, name: string, email: string, phone: ?string, role: string, is_active: bool}>
     */
    public function execute(?StaffRole $role = null, ?bool $isActive = null): array
    {
        $rows = [];
        foreach ($this->employees->list($role, $isActive) as $employee) {
            $rows[] = $this->presenter->present($employee);
        }

        return $rows;
    }
}
