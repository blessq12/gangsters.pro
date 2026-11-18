<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\Ingredient;
use App\Infrastructure\Warehouse\Model\WH_Ingredient;

final class IngredientMapper
{
    public function toDomain(WH_Ingredient $row): Ingredient
    {
        return Ingredient::restore(
            id: (int) $row->id,
            name: (string) $row->name,
            sku: $row->sku !== null && trim((string) $row->sku) !== '' ? (string) $row->sku : null,
            unit: (string) $row->unit,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Ingredient $ingredient): array
    {
        return [
            'name' => $ingredient->name(),
            'sku' => $ingredient->sku(),
            'unit' => $ingredient->unit(),
        ];
    }
}
