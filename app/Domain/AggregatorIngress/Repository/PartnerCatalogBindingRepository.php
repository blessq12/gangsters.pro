<?php

namespace App\Domain\AggregatorIngress\Repository;

use App\Domain\AggregatorIngress\ValueObject\ResolvedPartnerProduct;

interface PartnerCatalogBindingRepository
{
    public function resolve(string $partnerCode, string $partnerSku): ?ResolvedPartnerProduct;
}
