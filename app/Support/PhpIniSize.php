<?php

namespace App\Support;

final class PhpIniSize
{
    public static function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    public static function toMegabytesLabel(string $iniValue): string
    {
        $bytes = self::toBytes($iniValue);

        if ($bytes >= PHP_INT_MAX / 2) {
            return 'без ограничения';
        }

        return round($bytes / 1024 / 1024, 1).' MB';
    }
}
