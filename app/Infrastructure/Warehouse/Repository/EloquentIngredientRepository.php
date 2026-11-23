<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\Ingredient;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Infrastructure\Warehouse\Mapper\IngredientMapper;
use App\Infrastructure\Warehouse\Model\IngredientModel;

final class EloquentIngredientRepository implements IngredientRepository
{
    public function __construct(
        private readonly IngredientMapper $mapper,
    ) {}

    public function findById(int $id): ?Ingredient
    {
        $row = IngredientModel::query()->find($id);

        return $row instanceof IngredientModel ? $this->mapper->toDomain($row) : null;
    }

    public function findByName(string $name): ?Ingredient
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $row = IngredientModel::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        return $row instanceof IngredientModel ? $this->mapper->toDomain($row) : null;
    }

    public function listAll(): array
    {
        return $this->listPage(0, max(1, $this->countAll()));
    }

    public function listPage(int $offset, int $limit): array
    {
        return IngredientModel::query()
            ->orderBy('name')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (IngredientModel $row): Ingredient => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) IngredientModel::query()->count();
    }

    public function save(Ingredient $ingredient): void
    {
        $payload = $this->mapper->toPersistence($ingredient);

        if (! $ingredient->hasId()) {
            $row = IngredientModel::query()->create($payload);
            $ingredient->assignId((int) $row->id);

            return;
        }

        IngredientModel::query()->whereKey($ingredient->id())->update($payload);
    }
}
