<?php

namespace App\Domain\Supporting\Branch\Support;

/**
 * Нормализация расписания филиала: mon…sun → { work, is_day_off }.
 * work — строка «HH:MM-HH:MM» или null.
 */
final class ScheduleNormalizer
{
    /** @var list<string> */
    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    /**
     * @param  array<string, mixed>  $rawSchedule
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    public static function normalize(array $rawSchedule): array
    {
        $schedule = [];
        foreach (self::DAYS as $day) {
            $row = is_array($rawSchedule[$day] ?? null) ? $rawSchedule[$day] : [];
            $isDayOff = (bool) ($row['is_day_off'] ?? false);
            $work = self::nullableString($row['work'] ?? null);
            $schedule[$day] = [
                'work' => $isDayOff ? null : $work,
                'is_day_off' => $isDayOff,
            ];
        }

        return $schedule;
    }

    /**
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    public static function empty(): array
    {
        return self::normalize([]);
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
