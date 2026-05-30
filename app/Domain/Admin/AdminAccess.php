<?php

namespace App\Domain\Admin;

use App\Domain\Admin\Enums\AdminHub;
use App\Domain\Admin\Enums\AdminRole;
use App\Models\User;

final class AdminAccess
{
    public static function isStaff(?User $user): bool
    {
        return $user !== null && $user->admin_role instanceof AdminRole;
    }

    public static function canAccessHub(?User $user, AdminHub $hub): bool
    {
        if (! self::isStaff($user)) {
            return false;
        }

        return $user->admin_role->canAccessHub($hub);
    }

    public static function canMutate(?User $user): bool
    {
        if (! self::isStaff($user)) {
            return false;
        }

        return $user->admin_role->canMutate();
    }

    public static function canManageStaff(?User $user): bool
    {
        if (! self::isStaff($user)) {
            return false;
        }

        return $user->admin_role->canManageStaff();
    }

    /**
     * @return list<AdminRole>
     */
    public static function assignableRoles(?User $user): array
    {
        if (! self::canManageStaff($user)) {
            return [];
        }

        return array_values(array_filter(
            AdminRole::cases(),
            fn (AdminRole $role): bool => $user->admin_role->canAssignRole($role),
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function assignableRoleOptions(?User $user): array
    {
        $options = [];
        foreach (self::assignableRoles($user) as $role) {
            $options[$role->value] = $role->label();
        }

        return $options;
    }
}
