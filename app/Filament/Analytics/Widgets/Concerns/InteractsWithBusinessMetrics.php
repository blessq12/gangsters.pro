<?php

namespace App\Filament\Analytics\Widgets\Concerns;

use App\Application\Reporting\DTO\BusinessMetricsSnapshotDto;
use App\Application\Reporting\DTO\ClientsMetricsDto;
use App\Application\Reporting\DTO\FinanceMetricsDto;
use App\Application\Reporting\DTO\OrdersMetricsDto;
use App\Application\Reporting\DTO\OverviewMetricsDto;
use App\Application\Reporting\DTO\StorefrontMetricsDto;
use App\Application\Reporting\Query\BusinessMetricsReader;
use App\Application\Reporting\ValueObject\MetricsPeriod;
use InvalidArgumentException;

trait InteractsWithBusinessMetrics
{
    protected function resolveMetricsPeriod(): MetricsPeriod
    {
        $value = request()->query('period', MetricsPeriod::SevenDays->value);

        try {
            return MetricsPeriod::fromString((string) $value);
        } catch (InvalidArgumentException) {
            return MetricsPeriod::SevenDays;
        }
    }

    protected function overviewMetrics(): OverviewMetricsDto
    {
        return app(BusinessMetricsReader::class)->overview($this->resolveMetricsPeriod());
    }

    protected function financeMetrics(): FinanceMetricsDto
    {
        return app(BusinessMetricsReader::class)->finance($this->resolveMetricsPeriod());
    }

    protected function clientsMetrics(): ClientsMetricsDto
    {
        return app(BusinessMetricsReader::class)->clients($this->resolveMetricsPeriod());
    }

    protected function ordersMetrics(): OrdersMetricsDto
    {
        return app(BusinessMetricsReader::class)->orders($this->resolveMetricsPeriod());
    }

    protected function storefrontMetrics(): StorefrontMetricsDto
    {
        return app(BusinessMetricsReader::class)->storefront($this->resolveMetricsPeriod());
    }

    protected function businessMetricsSnapshot(): BusinessMetricsSnapshotDto
    {
        return app(BusinessMetricsReader::class)->forPeriod($this->resolveMetricsPeriod());
    }
}
