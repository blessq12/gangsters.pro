<?php

namespace App\Application\Staff\Support;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Выдача/ревокация Sanctum PAT для User (= Employee.id).
 */
final class EmployeeAccessToken
{
    public static function issue(int $employeeId, string $tokenName = 'backoffice'): string
    {
        $user = User::query()->find($employeeId);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        return $user->createToken($tokenName)->plainTextToken;
    }

    public static function revokeCurrent(?User $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
