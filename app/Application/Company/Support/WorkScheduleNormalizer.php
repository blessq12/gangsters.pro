<?php

namespace App\Application\Company\Support;

final class WorkScheduleNormalizer
{
    /**
     * @param  array<int, array<string, mixed>>|null  $schedule
     * @return array<int, array<string, mixed>>|null
     */
    public static function normalizeForStorage(?array $schedule): ?array
    {
        if ($schedule === null) {
            return null;
        }

        $out = [];
        foreach ($schedule as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = $row;
        }

        return $out === [] ? null : $out;
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $schedule
     * @return array<int, array<string, mixed>>|null
     */
    public static function sanitizeForPublic(?array $schedule): ?array
    {
        if ($schedule === null) {
            return null;
        }

        $out = [];
        foreach ($schedule as $row) {
            if (! is_array($row)) {
                continue;
            }
            unset($row['delivery']);
            $out[] = $row;
        }

        return $out;
    }
}
