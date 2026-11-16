<?php

namespace App\Infrastructure\Staff\Port;

use App\Domain\Staff\Port\StaffSessionPort;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class LaravelStaffSessionAdapter implements StaffSessionPort
{
    public function login(int $employeeId): string
    {
        $user = User::query()->find($employeeId);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        return $user->createToken('backoffice')->plainTextToken;
    }

    public function logout(): void
    {
        $user = request()->user('sanctum');
        if (! $user instanceof User) {
            return;
        }

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Auth::guard('sanctum')->forgetUser();
    }

    public function currentEmployeeId(): ?int
    {
        $user = request()->user('sanctum');
        if (! $user instanceof User) {
            return null;
        }

        $token = $user->currentAccessToken();
        if (
            ! $token instanceof PersonalAccessToken
            || ! PersonalAccessToken::query()->whereKey($token->getKey())->exists()
        ) {
            return null;
        }

        return (int) $user->id;
    }
}
