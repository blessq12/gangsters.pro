<?php

namespace App\Application\Order\OrderDraft\DTO;

final readonly class PlaceOrderInput
{
    public function __construct(
        public string $clientRequestId,
        public OrderDraftInput $draft,
    ) {}
}
