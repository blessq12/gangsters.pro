<?php

namespace App\Infrastructure\LegacyMigration;

final class LegacyPhoneNormalizer
{
    public function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';
        if ($digits === '') {
            return $trimmed;
        }

        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return $trimmed;
        }

        $code = substr($digits, 0, 3);
        $part1 = substr($digits, 3, 3);
        $part2 = substr($digits, 6, 2);
        $part3 = substr($digits, 8, 2);

        return sprintf('+7 (%s) %s-%s-%s', $code, $part1, $part2, $part3);
    }
}
