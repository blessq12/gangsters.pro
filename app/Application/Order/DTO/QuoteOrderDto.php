<?php

namespace App\Application\Order\DTO;

final readonly class QuoteOrderDto
{
    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @param  list<int>  $complementProductIds
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>|null  $address
     */
    public function __construct(
        public array $lines,
        public string $deliveryMethod,
        public array $client,
        public ?array $address = null,
        public ?string $deliveryComment = null,
        public ?string $scheduledAt = null,
        public string $paymentMethod = 'cash',
        public ?int $changeFromRubles = null,
        public ?int $giftProductId = null,
        public array $complementProductIds = [],
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}
}
