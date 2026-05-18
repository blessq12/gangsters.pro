<?php

namespace App\Application\Reporting\ValueObject;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

enum MetricsPeriod: string
{
    case Today = 'today';
    case SevenDays = '7d';
    case ThirtyDays = '30d';
    case MonthToDate = 'mtd';

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Сегодня',
            self::SevenDays => '7 дней',
            self::ThirtyDays => '30 дней',
            self::MonthToDate => 'С начала месяца',
        };
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException("Unknown metrics period: {$value}");
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function currentRange(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        return match ($this) {
            self::Today => [
                'from' => $now->startOfDay(),
                'to' => $now->endOfDay(),
            ],
            self::SevenDays => [
                'from' => $now->subDays(6)->startOfDay(),
                'to' => $now->endOfDay(),
            ],
            self::ThirtyDays => [
                'from' => $now->subDays(29)->startOfDay(),
                'to' => $now->endOfDay(),
            ],
            self::MonthToDate => [
                'from' => $now->startOfMonth()->startOfDay(),
                'to' => $now->endOfDay(),
            ],
        };
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function previousRange(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $current = $this->currentRange($now);

        return match ($this) {
            self::Today => [
                'from' => $current['from']->subDay(),
                'to' => $current['to']->subDay(),
            ],
            self::SevenDays => [
                'from' => $current['from']->subDays(7),
                'to' => $current['from']->subSecond(),
            ],
            self::ThirtyDays => [
                'from' => $current['from']->subDays(30),
                'to' => $current['from']->subSecond(),
            ],
            self::MonthToDate => [
                'from' => $current['from']->subMonth()->startOfMonth(),
                'to' => $current['from']->subSecond(),
            ],
        };
    }

    public function trendGranularity(): string
    {
        return $this === self::Today ? 'hour' : 'day';
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
