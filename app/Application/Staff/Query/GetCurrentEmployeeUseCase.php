<?php

namespace App\Application\Staff\Query;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;

final class GetCurrentEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{employee: ?array<string, mixed>, auth_required: bool}
     */
    public function execute(?int $userId): array
    {
        $employee = null;

        if ($userId !== null) {
            $found = $this->employees->findById($userId);
            if ($found !== null) {
                $employee = $this->presenter->present($found);
            }
        }

        return [
            'employee' => $employee,
            'auth_required' => (bool) config('backoffice.auth_required'),
        ];
    }
}
