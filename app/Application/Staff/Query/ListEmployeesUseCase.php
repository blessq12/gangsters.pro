<?php

namespace App\Application\Staff\Query;

use App\Application\Shared\PageParams;
use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Domain\Generic\Staff\Repository\EmployeeRepository;

final class ListEmployeesUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        ?string $role = null,
        ?bool $isActive = null,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        $staffRole = null;
        if ($role !== null && trim($role) !== '') {
            $staffRole = StaffRole::tryFrom(trim($role));
            if (! $staffRole instanceof StaffRole) {
                throw new \InvalidArgumentException('Некорректная роль.');
            }
        }

        return PageParams::wrap(
            $this->presenter->presentList(
                $this->employees->listPage($offset, $limit, $staffRole, $isActive),
            ),
            $this->employees->count($staffRole, $isActive),
            $offset,
            $limit,
        );
    }
}
