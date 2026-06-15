<?php

namespace App\Domain\AggregatorIngress\ValueObject;

final readonly class ResolvedPartnerProduct
{
    public function __construct(
        public int $productId,
        public string $productName,
        public ?string $sku = null,
    ) {}
}
