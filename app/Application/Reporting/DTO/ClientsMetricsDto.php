<?php

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\ValueObject\MetricsPeriod;

final readonly class ClientsMetricsDto implements MetricsSectionDto
{
    /**
     * @param  array{
     *     new_clients: int,
     *     total_clients: int,
     *     marketing_consent: int,
     *     repeat_clients: int,
     *     previous_new_clients: int
     * }  $clientsKpi
     * @param  list<array{client_id: int, client_name: string, orders_count: int, revenue: int}>  $topClients
     */
    public function __construct(
        public MetricsPeriod $period,
        public array $clientsKpi,
        public array $topClients,
    ) {}
}
