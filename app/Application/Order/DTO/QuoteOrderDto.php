<?php

namespace App\Application\Order\DTO;

final readonly class QuoteOrderDto
{
    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @param  array{kind: string, id?: int|null, name?: string|null, phone?: string|null, email?: string|null}  $client
     * @param  array{street?: string, house?: string, entrance?: string|null, floor?: string|null, apartment?: string|null, comment?: string|null, lat?: float|null, lng?: float|null}|null  $address
     * @param  array<int, int>  $complementSelections
     */
    public function __construct(
        public array $lines,
        public string $deliveryMethod,
        public array $client,
        public ?array $address,
        public ?string $scheduledAt,
        public string $paymentMethod,
        public ?int $changeFrom,
        public ?int $giftProductId,
        public array $complementSelections,
        public ?string $promoCode = null,
    ) {}
}
