<?php

namespace App\Support;

/**
 * Денежные суммы в проекте: в домене и БД — целые копейки (int).
 * В публичном API и UI — рубли как число с двумя знаками после запятой (float).
 */
final class Money
{
    public const SCALE = 2;

    public static function kopecksToApiRubles(int $kopecks): float
    {
        return round($kopecks / 100, self::SCALE);
    }

    /**
     * @param  float|string|null  $rubles  Допускается строка с запятой как десятичным разделителем
     */
    public static function apiRublesToKopecks(float|string|null $rubles): ?int
    {
        if ($rubles === null || $rubles === '') {
            return null;
        }

        $normalized = is_string($rubles)
            ? str_replace([' ', ','], ['', '.'], trim($rubles))
            : $rubles;

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        $value = (float) $normalized;
        if ($value < 0) {
            return null;
        }

        return (int) round($value * 100);
    }

    /** Всегда два знака после запятой (для контрактов, где нужна фиксированная ширина). */
    public static function formatApiRubles(float $rubles): string
    {
        return number_format(round($rubles, self::SCALE), self::SCALE, ',', ' ');
    }

    /** Для UI/писем: без «,00» у целых рублей. */
    public static function formatRublesRuAdaptive(float $rubles): string
    {
        $rounded = round($rubles, self::SCALE);
        $minor = (int) round($rounded * 100) % 100;
        $decimals = $minor === 0 ? 0 : self::SCALE;

        return number_format($rounded, $decimals, ',', ' ');
    }

    public static function formatKopecksForAdmin(int $kopecks): string
    {
        return self::formatRublesRuAdaptive(self::kopecksToApiRubles($kopecks)).' ₽';
    }
}
