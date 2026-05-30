<?php

namespace Tests\Unit\Reporting;

use App\Application\Reporting\DTO\FinanceMetricsDto;
use App\Application\Reporting\DTO\OverviewMetricsDto;
use App\Application\Reporting\Query\BusinessMetricsReader;
use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Application\Reporting\ValueObject\MetricsSection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class BusinessMetricsSectionTest extends TestCase
{
    public function test_finance_section_returns_typed_dto(): void
    {
        Cache::flush();

        $dto = app(BusinessMetricsReader::class)->forSection(
            MetricsPeriod::SevenDays,
            MetricsSection::Finance,
        );

        $this->assertInstanceOf(FinanceMetricsDto::class, $dto);
        $this->assertArrayHasKey('paid_revenue', $dto->revenueKpi);
        $this->assertArrayHasKey('labels', $dto->revenueTrend);
    }

    public function test_overview_section_uses_separate_cache_key(): void
    {
        Cache::flush();

        $reader = app(BusinessMetricsReader::class);
        $overview = $reader->overview(MetricsPeriod::SevenDays);

        $this->assertInstanceOf(OverviewMetricsDto::class, $overview);

        $cacheKey = sprintf(
            'business_metrics:%s:%s:%s',
            MetricsSection::Overview->value,
            MetricsPeriod::SevenDays->value,
            now()->format('Y-m-d-H'),
        );

        $this->assertTrue(Cache::has($cacheKey));
    }
}
