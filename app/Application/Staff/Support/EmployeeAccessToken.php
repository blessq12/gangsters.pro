<?php

namespace App\Application\Staff\Support;

use App\Infrastructure\Staff\Model\STF_Employee;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Выдача/ревокация Sanctum PAT для STF_Employee.
 */
final class EmployeeAccessToken
{
    public static function issue(int $employeeId, string $tokenName = 'backoffice'): string
    {
        $employee = STF_Employee::query()->find($employeeId);
        if (! $employee instanceof STF_Employee) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        return $employee->createToken($tokenName)->plainTextToken;
    }

    public static function revokeCurrent(?STF_Employee $employee): void
    {
        if (! $employee instanceof STF_Employee) {
            return;
        }

        $token = $employee->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
