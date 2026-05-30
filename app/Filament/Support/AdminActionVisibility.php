<?php

namespace App\Filament\Support;

use App\Domain\Admin\AdminAccess;
use App\Models\User;

final class AdminActionVisibility
{
    public static function canMutate(): bool
    {
        $user = auth()->user();

        return $user instanceof User && AdminAccess::canMutate($user);
    }

    public static function canManageStaff(): bool
    {
        $user = auth()->user();

        return $user instanceof User && AdminAccess::canManageStaff($user);
    }
}
