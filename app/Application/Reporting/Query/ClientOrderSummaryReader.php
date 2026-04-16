<?php

namespace App\Application\Reporting\Query;

interface ClientOrderSummaryReader
{
    /**
     * @return array<string, mixed>|null
     */
    public function getSummaryById(int $clientId): ?array;
}
