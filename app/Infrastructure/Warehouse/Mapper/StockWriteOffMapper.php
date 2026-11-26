<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Infrastructure\Warehouse\Model\StockWriteOffModel;
use DateTimeImmutable;

final class StockWriteOffMapper
{
    public function toDomain(StockWriteOffModel $row): StockWriteOff
    {
        return StockWriteOff::restore(
            id: (int) $row->id,
            branchId: (int) $row->branch_id,
            ingredientId: (int) $row->ingredient_id,
            quantity: (float) $row->quantity,
            reason: (string) $row->reason,
            kitchenTicketId: $row->kitchen_ticket_id !== null ? (int) $row->kitchen_ticket_id : null,
            occurredAt: $row->occurred_at !== null
                ? DateTimeImmutable::createFromInterface($row->occurred_at)
                : new DateTimeImmutable,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(StockWriteOff $writeOff): array
    {
        return [
            'branch_id' => $writeOff->branchId(),
            'ingredient_id' => $writeOff->ingredientId(),
            'quantity' => $writeOff->quantity(),
            'reason' => $writeOff->reason(),
            'kitchen_ticket_id' => $writeOff->kitchenTicketId(),
            'occurred_at' => $writeOff->occurredAt()->format('Y-m-d H:i:s'),
        ];
    }
}
