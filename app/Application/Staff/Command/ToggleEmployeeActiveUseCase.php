<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;

final class ToggleEmployeeActiveUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $employee = $this->employees->findById($id);
        if ($employee === null) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        if ($employee->isActive()) {
            $employee->deactivate();
        } else {
            $employee->reactivate();
        }

        $this->employees->save($employee);

        return $this->presenter->present($employee);
    }
}
