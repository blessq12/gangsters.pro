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
     * @return list<array<string, mixed>>
     */
    public function execute(?string $role = null, ?bool $isActive = null): array
    {
        $staffRole = null;
        if ($role !== null && trim($role) !== '') {
            $staffRole = StaffRole::tryFrom(trim($role));
            if (! $staffRole instanceof StaffRole) {
                throw new \InvalidArgumentException('Некорректная роль.');
            }
        }

        return $this->presenter->presentList(
            $this->employees->list($staffRole, $isActive),
        );
    }
}
