<?php

namespace App\Application\Order\OrderDraft\DTO;

final readonly class OrderDraftInput
{
    /**
     * @param  list<array{product_id: int, quantity: int, payload: array<string, mixed>|null}>  $cartLines
     * @param  array<string, mixed>|null  $client
     * @param  array<string, mixed>|null  $delivery
     * @param  array<string, mixed>|null  $payment
     */
    public function __construct(
        public array $cartLines,
        public ?int $selectedGiftProductId,
        public ?array $client,
        public ?array $delivery,
        public ?array $payment,
    ) {}
}
