<?php

namespace App\Application\Staff\Query;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Port\StaffSessionPort;
use App\Domain\Staff\Repository\EmployeeRepository;

final class GetCurrentEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly StaffSessionPort $session,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{id: int, name: string, email: string, phone: ?string, role: string, is_active: bool}|null
     */
    public function execute(): ?array
    {
        $id = $this->session->currentEmployeeId();
        if ($id === null) {
            return null;
        }

        $employee = $this->employees->findById($id);
        if (! $employee instanceof Employee || ! $employee->isActive()) {
            return null;
        }

        return $this->presenter->present($employee);
    }
}
