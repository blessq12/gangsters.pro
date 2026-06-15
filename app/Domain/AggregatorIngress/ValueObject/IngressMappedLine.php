<?php

namespace App\Domain\AggregatorIngress\ValueObject;

final readonly class IngressMappedLine
{
    public function __construct(
        public string $partnerSku,
        public int $quantity,
        public int $unitPriceRubles,
    ) {}
}
