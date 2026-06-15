<?php

namespace App\Application\AggregatorIngress\Port;

use App\Domain\AggregatorIngress\ValueObject\IngressMappedOrder;

interface IngressPartnerAdapter
{
    public function partnerCode(): string;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractExternalOrderId(array $payload): string;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): IngressMappedOrder;
}
