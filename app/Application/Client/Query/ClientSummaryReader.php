<?php

namespace App\Application\Client\Query;

interface ClientSummaryReader
{
    /**
     * @return array<string, mixed>|null
     */
    public function getSummaryById(int $clientId): ?array;
}

