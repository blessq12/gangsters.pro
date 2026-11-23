<?php

namespace App\Application\Staff\Query;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;

final class GetEmployeeUseCase
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
        if ($id < 1) {
            throw new \InvalidArgumentException('Некорректный id сотрудника.');
        }

        $employee = $this->employees->findById($id);
        if ($employee === null) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        return $this->presenter->present($employee);
    }
}
