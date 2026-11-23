<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Support\EmployeeAccessToken;
use App\Infrastructure\Staff\Model\EmployeeModel;

final class LogoutEmployeeUseCase
{
    /**
     * @return array{ok: true}
     */
    public function execute(?EmployeeModel $employee): array
    {
        EmployeeAccessToken::revokeCurrent($employee);

        return ['ok' => true];
    }
}
