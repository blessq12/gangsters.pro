<?php

namespace App\Infrastructure\LegacyMigration;

final class LegacyOrderStatusMapper
{
    public function map(int $legacyStatus): string
    {
        return match ($legacyStatus) {
            10 => 'in_transit',
            11, 1 => 'delivered',
            default => 'delivered',
        };
    }
}
