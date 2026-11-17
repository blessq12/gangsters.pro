<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Support\EmployeeAccessToken;
use App\Models\User;

final class LogoutEmployeeUseCase
{
    /**
     * @return array{ok: true}
     */
    public function execute(?User $user): array
    {
        EmployeeAccessToken::revokeCurrent($user);

        return ['ok' => true];
    }
}
