<?php

namespace App\Support\Reporting;

final class BusinessMetricsComparison
{
    public static function percentChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public static function formatDeltaDescription(int $current, int $previous): string
    {
        $percent = self::percentChange($current, $previous);

        if ($percent === null) {
            return $current > 0 ? 'Нет данных за прошлый период' : 'Без изменений';
        }

        if ($percent === 0.0) {
            return 'Без изменений к прошлому периоду';
        }

        $sign = $percent > 0 ? '+' : '';

        return "{$sign}{$percent}% к прошлому периоду";
    }
}
