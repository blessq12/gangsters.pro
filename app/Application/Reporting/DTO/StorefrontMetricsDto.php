<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;

final readonly class StorefrontMetricsDto implements MetricsSectionDto
{
    /**
     * @param  array{
     *     active_sessions: int,
     *     cart_lines: int,
     *     checkout_drafts: int
     * }  $shoppingFunnel
     * @param  list<array{product_original_id: ?int, product_name: string, quantity: int, revenue: int}>  $topProducts
     */
    public function __construct(
        public MetricsPeriod $period,
        public array $shoppingFunnel,
        public array $topProducts,
    ) {}
}
