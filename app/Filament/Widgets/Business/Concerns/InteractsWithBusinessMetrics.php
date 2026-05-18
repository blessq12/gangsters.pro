<?php

namespace App\Filament\Widgets\Business\Concerns;

use App\Application\Reporting\DTO\BusinessMetricsSnapshotDto;
use App\Application\Reporting\Query\BusinessMetricsReader;
use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Support\Reporting\BusinessMetricsComparison;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait InteractsWithBusinessMetrics
{
    use InteractsWithPageFilters;

    protected function metricsPeriod(): MetricsPeriod
    {
        $value = $this->pageFilters['period'] ?? MetricsPeriod::SevenDays->value;

        return MetricsPeriod::tryFrom((string) $value) ?? MetricsPeriod::SevenDays;
    }

    protected function businessSnapshot(): BusinessMetricsSnapshotDto
    {
        return app(BusinessMetricsReader::class)->forPeriod($this->metricsPeriod());
    }

    protected function formatDeltaDescription(int $current, int $previous): string
    {
        return BusinessMetricsComparison::formatDeltaDescription($current, $previous);
    }
}
