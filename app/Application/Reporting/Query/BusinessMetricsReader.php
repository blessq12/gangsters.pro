<?php

namespace App\Application\Reporting\Query;

use App\Application\Reporting\DTO\BusinessMetricsSnapshotDto;
use App\Application\Reporting\DTO\ClientsMetricsDto;
use App\Application\Reporting\DTO\FinanceMetricsDto;
use App\Application\Reporting\DTO\MetricsSectionDto;
use App\Application\Reporting\DTO\OrdersMetricsDto;
use App\Application\Reporting\DTO\OverviewMetricsDto;
use App\Application\Reporting\DTO\StorefrontMetricsDto;
use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Application\Reporting\ValueObject\MetricsSection;

interface BusinessMetricsReader
{
    public function forPeriod(MetricsPeriod $period): BusinessMetricsSnapshotDto;

    public function forSection(MetricsPeriod $period, MetricsSection $section): MetricsSectionDto;

    public function overview(MetricsPeriod $period): OverviewMetricsDto;

    public function finance(MetricsPeriod $period): FinanceMetricsDto;

    public function clients(MetricsPeriod $period): ClientsMetricsDto;

    public function orders(MetricsPeriod $period): OrdersMetricsDto;

    public function storefront(MetricsPeriod $period): StorefrontMetricsDto;
}
