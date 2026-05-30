<?php

namespace App\Application\Company\Support;

use App\Application\Common\Exceptions\ApiException;

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

            unset($row['delivery']);

            $day = isset($row['day']) ? (int) $row['day'] : 0;
            if ($day < 1 || $day > 7) {
                throw new ApiException('День расписания должен быть от 1 до 7.', 422);
            }

            $isDayOff = (bool) ($row['is_day_off'] ?? false);
            $work = isset($row['work']) ? trim((string) $row['work']) : '';

            if (! $isDayOff && $work === '') {
                throw new ApiException('Укажите часы работы или отметьте выходной.', 422);
            }

            $out[] = [
                'day' => $day,
                'work' => $isDayOff ? '' : $work,
                'is_day_off' => $isDayOff,
            ];
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
