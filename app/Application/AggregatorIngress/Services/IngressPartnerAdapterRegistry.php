<?php

namespace App\Application\AggregatorIngress\Services;

use App\Application\AggregatorIngress\Port\IngressPartnerAdapter;
use App\Domain\AggregatorIngress\Exception\PartnerNotConfiguredException;

final class IngressPartnerAdapterRegistry
{
    /**
     * @param  iterable<IngressPartnerAdapter>  $adapters
     */
    public function __construct(
        private readonly iterable $adapters,
    ) {}

    public function resolve(string $partnerCode): IngressPartnerAdapter
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->partnerCode() === $partnerCode) {
                return $adapter;
            }
        }

        throw new PartnerNotConfiguredException($partnerCode);
    }
}
