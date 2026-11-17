<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\Ingredient;
use App\Domain\Warehouse\Repository\IngredientRepository;
use App\Infrastructure\Warehouse\Mapper\IngredientMapper;
use App\Infrastructure\Warehouse\Model\WH_Ingredient;

final class EloquentIngredientRepository implements IngredientRepository
{
    public function __construct(
        private readonly IngredientMapper $mapper,
    ) {}

    public function findById(int $id): ?Ingredient
    {
        $row = WH_Ingredient::query()->find($id);

        return $row instanceof WH_Ingredient ? $this->mapper->toDomain($row) : null;
    }

    public function listAll(): array
    {
        return WH_Ingredient::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (WH_Ingredient $row): Ingredient => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(Ingredient $ingredient): void
    {
        $payload = $this->mapper->toPersistence($ingredient);

        if (! $ingredient->hasId()) {
            $row = WH_Ingredient::query()->create($payload);
            $ingredient->assignId((int) $row->id);

            return;
        }

        WH_Ingredient::query()->whereKey($ingredient->id())->update($payload);
    }
}
