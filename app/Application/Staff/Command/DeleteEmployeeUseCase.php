<?php

namespace App\Application\Staff\Command;

use App\Domain\Generic\Staff\Repository\EmployeeRepository;

final class DeleteEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $id, int $currentEmployeeId): array
    {
        if ($id === $currentEmployeeId) {
            throw new \InvalidArgumentException('Нельзя удалить себя.');
        }

        if ($this->employees->findById($id) === null) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $this->employees->delete($id);

        return ['ok' => true];
    }
}
