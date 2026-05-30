<?php

namespace App\Filament\Support;

use App\Support\Reporting\BusinessMetricsComparison;

final class BusinessMetricsViewHelper
{
    public static function formatRubles(int $amountKopecks): string
    {
        $rubles = $amountKopecks / 100;

        return number_format($rubles, $rubles === floor($rubles) ? 0 : 2, ',', ' ').' ₽';
    }

    public static function formatInteger(int $value): string
    {
        return number_format($value, 0, ',', ' ');
    }

    public static function deltaDescription(int $current, int $previous): string
    {
        return BusinessMetricsComparison::formatDeltaDescription($current, $previous);
    }
}
