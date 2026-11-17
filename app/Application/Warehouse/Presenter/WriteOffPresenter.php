<?php

namespace App\Application\Warehouse\Presenter;

use App\Domain\Warehouse\Entity\StockWriteOff;

final class WriteOffPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(StockWriteOff $writeOff): array
    {
        return [
            'id' => $writeOff->hasId() ? $writeOff->id() : null,
            'ingredient_id' => $writeOff->ingredientId(),
            'quantity' => $writeOff->quantity(),
            'reason' => $writeOff->reason(),
            'kitchen_ticket_id' => $writeOff->kitchenTicketId(),
            'occurred_at' => $writeOff->occurredAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param  list<StockWriteOff>  $writeOffs
     * @return list<array<string, mixed>>
     */
    public function presentList(array $writeOffs): array
    {
        return array_map(fn (StockWriteOff $writeOff): array => $this->present($writeOff), $writeOffs);
    }
}
