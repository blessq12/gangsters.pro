<?php

namespace App\Application\Staff\Support;

use App\Infrastructure\Staff\Model\EmployeeModel;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Выдача/ревокация Sanctum PAT для EmployeeModel.
 */
final class EmployeeAccessToken
{
    public static function issue(int $employeeId, string $tokenName = 'backoffice'): string
    {
        $employee = EmployeeModel::query()->find($employeeId);
        if (! $employee instanceof EmployeeModel) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        return $employee->createToken($tokenName)->plainTextToken;
    }

    public static function revokeCurrent(?EmployeeModel $employee): void
    {
        if (! $employee instanceof EmployeeModel) {
            return;
        }

        $token = $employee->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
