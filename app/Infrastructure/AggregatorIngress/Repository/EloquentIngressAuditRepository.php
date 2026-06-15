<?php

namespace App\Infrastructure\AggregatorIngress\Repository;

use App\Domain\AggregatorIngress\Repository\IngressAuditRepository;
use App\Infrastructure\AggregatorIngress\Model\ING_IngressAudit;
use DateTimeImmutable;

final class EloquentIngressAuditRepository implements IngressAuditRepository
{
    public function record(
        string $partnerCode,
        string $externalOrderId,
        string $result,
        array $rawPayload,
        ?int $orderId = null,
    ): void {
        ING_IngressAudit::query()->create([
            'partner_code' => $partnerCode,
            'external_order_id' => $externalOrderId,
            'order_id' => $orderId,
            'result' => $result,
            'raw_payload' => $rawPayload,
            'created_at' => new DateTimeImmutable(),
        ]);
    }
}
