<?php

namespace App\Domain\AggregatorIngress\Repository;

use App\Domain\AggregatorIngress\ValueObject\ResolvedPartnerProduct;

interface PartnerCatalogBindingRepository
{
    public function resolve(string $partnerCode, string $partnerSku): ?ResolvedPartnerProduct;

    /**
     * @return list<array{partner_sku: string, product_id: int}>
     */
    public function listByPartner(string $partnerCode): array;
}
