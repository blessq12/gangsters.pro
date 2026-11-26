<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\TechCard;
use App\Domain\Supporting\Catalog\Repository\TechCardRepository;
use App\Infrastructure\Catalog\Mapper\TechCardMapper;
use App\Infrastructure\Catalog\Model\TechCardLineModel;
use App\Infrastructure\Catalog\Model\TechCardModel;
use Illuminate\Support\Facades\DB;

final class EloquentTechCardRepository implements TechCardRepository
{
    public function __construct(
        private readonly TechCardMapper $mapper,
    ) {}

    public function findByProductId(int $productId): ?TechCard
    {
        $row = TechCardModel::query()
            ->with('lines')
            ->where('product_id', $productId)
            ->first();

        return $row instanceof TechCardModel ? $this->mapper->toDomain($row) : null;
    }

    public function findByProductIds(array $productIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $productIds),
            static fn (int $id): bool => $id > 0,
        )));
        if ($ids === []) {
            return [];
        }

        $result = [];
        $rows = TechCardModel::query()
            ->with('lines')
            ->whereIn('product_id', $ids)
            ->get();

        foreach ($rows as $row) {
            if (! $row instanceof TechCardModel) {
                continue;
            }
            $card = $this->mapper->toDomain($row);
            $result[$card->productId()] = $card;
        }

        return $result;
    }

    public function save(TechCard $techCard): void
    {
        DB::transaction(function () use ($techCard): void {
            if ($techCard->isEmpty()) {
                $this->deleteByProductId($techCard->productId());

                return;
            }

            $card = TechCardModel::query()->firstOrCreate(
                ['product_id' => $techCard->productId()],
            );
            TechCardLineModel::query()->where('tech_card_id', $card->id)->delete();

            foreach (array_values($techCard->lines()) as $index => $line) {
                TechCardLineModel::query()->create([
                    'tech_card_id' => (int) $card->id,
                    'ingredient_ref' => $line->ingredientRef(),
                    'quantity' => $line->quantity(),
                    'unit' => $line->unit(),
                    'sort_order' => $index,
                ]);
            }
        });
    }

    public function deleteByProductId(int $productId): void
    {
        TechCardModel::query()->where('product_id', $productId)->delete();
    }
}
