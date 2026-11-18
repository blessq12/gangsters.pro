<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Support\EmployeeAccessToken;
use App\Infrastructure\Staff\Model\STF_Employee;

final class LogoutEmployeeUseCase
{
    /**
     * @return array{ok: true}
     */
    public function execute(?STF_Employee $employee): array
    {
        EmployeeAccessToken::revokeCurrent($employee);

        return ['ok' => true];
    }
}
