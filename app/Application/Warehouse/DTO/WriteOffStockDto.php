<?php

namespace App\Application\Warehouse\DTO;

final readonly class WriteOffStockDto
{
    public function __construct(
        public int $branchId,
        public int $ingredientId,
        public float $quantity,
        public string $reason,
        public ?int $kitchenTicketId = null,
    ) {}
}
