<?php

namespace App\Domain\AggregatorIngress\Repository;

interface IngressAuditRepository
{
    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function record(
        string $partnerCode,
        string $externalOrderId,
        string $result,
        array $rawPayload,
        ?int $orderId = null,
    ): void;
}
