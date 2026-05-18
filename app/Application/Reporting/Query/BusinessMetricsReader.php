<?php

namespace App\Application\Reporting\Query;

use App\Application\Reporting\DTO\BusinessMetricsSnapshotDto;
use App\Application\Reporting\ValueObject\MetricsPeriod;

interface BusinessMetricsReader
{
    public function forPeriod(MetricsPeriod $period): BusinessMetricsSnapshotDto;
}
