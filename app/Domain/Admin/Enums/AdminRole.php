<?php

namespace App\Domain\Admin\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Operations = 'operations';
    case Catalog = 'catalog';
    case Company = 'company';
    case ReadOnly = 'read_only';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Суперадмин',
            self::Operations => 'Операции',
            self::Catalog => 'Каталог',
            self::Company => 'Компания',
            self::ReadOnly => 'Только просмотр',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * @return list<AdminHub>
     */
    public function hubs(): array
    {
        return match ($this) {
            self::SuperAdmin => AdminHub::cases(),
            self::Operations => [AdminHub::Analytics, AdminHub::Operations],
            self::Catalog => [AdminHub::Catalog, AdminHub::Marketing],
            self::Company => [AdminHub::Company],
            self::ReadOnly => AdminHub::cases(),
        };
    }

    public function canAccessHub(AdminHub $hub): bool
    {
        return in_array($hub, $this->hubs(), true);
    }

    public function canMutate(): bool
    {
        return $this !== self::ReadOnly;
    }

    public function canManageStaff(): bool
    {
        return match ($this) {
            self::SuperAdmin, self::Company => true,
            default => false,
        };
    }

    public function canAssignRole(self $role): bool
    {
        if ($this === self::SuperAdmin) {
            return true;
        }

        if ($this === self::Company) {
            return $role !== self::SuperAdmin;
        }

        return false;
    }
}
