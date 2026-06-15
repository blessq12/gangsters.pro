<?php

namespace App\Application\AggregatorIngress\DTO;

final readonly class ReceiveExternalOrderDto
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $partnerCode,
        public ?string $apiKey,
        public array $payload,
    ) {}
}
