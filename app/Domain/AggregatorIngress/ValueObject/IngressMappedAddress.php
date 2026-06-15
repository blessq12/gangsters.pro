<?php

namespace App\Domain\AggregatorIngress\ValueObject;

final readonly class IngressMappedAddress
{
    public function __construct(
        public string $street,
        public string $house,
        public ?string $entrance,
        public ?string $apartment,
    ) {}
}
