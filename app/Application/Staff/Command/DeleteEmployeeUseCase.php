<?php

namespace App\Application\Staff\Command;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Repository\EmployeeRepository;

final class DeleteEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
    ) {}

    public function execute(int $id): void
    {
        $employee = $this->employees->findById($id);
        if (! $employee instanceof Employee) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $this->employees->delete($id);
    }
}
