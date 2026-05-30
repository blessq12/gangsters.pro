<?php

namespace App\Filament\Support\Concerns;

use App\Domain\Admin\AdminAccess;
use App\Domain\Admin\Enums\AdminHub;
use App\Models\User;

trait AuthorizesAdminHub
{
    abstract protected static function adminHub(): AdminHub;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && AdminAccess::canAccessHub($user, static::adminHub());
    }

    public static function canCreate(): bool
    {
        return AdminAccess::canMutate(auth()->user()) && static::canAccess();
    }

    public static function canEdit($record): bool
    {
        return static::canCreate();
    }

    public static function canDelete($record): bool
    {
        return static::canCreate();
    }
}
