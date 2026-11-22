<?php

namespace App\Domain\Supporting\Warehouse\Port;

/**
 * Снимок потребностей по техкартам. Warehouse Domain не импортирует Catalog/Company.
 *
 * @phpstan-type TicketLineSnapshot array{entry_id: int, quantity: int}
 * @phpstan-type IngredientNeed array{ingredient_ref: string, quantity: float, unit: string}
 */
interface ProductRecipeSnapshotPort
{
    /**
     * Раскрыть позиции тикета (товар или сет филиала) в потребности ингредиентов.
     *
     * @param  list<TicketLineSnapshot>  $lines
     * @return list<IngredientNeed>
     */
    public function resolveNeeds(int $branchId, array $lines): array;
}
