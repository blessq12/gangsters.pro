<?php

namespace App\Infrastructure\Staff\Port;

use App\Domain\Staff\Port\StaffSessionPort;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class LaravelStaffSessionAdapter implements StaffSessionPort
{
    public function login(int $employeeId): void
    {
        $user = User::query()->find($employeeId);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        Auth::guard('web')->login($user);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        $request = request();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    public function currentEmployeeId(): ?int
    {
        $id = Auth::guard('web')->id();

        return $id === null ? null : (int) $id;
    }
}
