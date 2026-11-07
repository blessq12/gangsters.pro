<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\Promotion;
use App\Domain\Content\Repository\PromotionRepository;
use App\Infrastructure\Content\Mapper\PromotionMapper;
use App\Infrastructure\Content\Model\MKT_Promotion;

final class EloquentPromotionRepository implements PromotionRepository
{
    public function __construct(
        private readonly PromotionMapper $mapper,
    ) {}

    public function findActiveOrdered(): array
    {
        return MKT_Promotion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MKT_Promotion $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return MKT_Promotion::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MKT_Promotion $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Promotion
    {
        $row = $id === null ? new MKT_Promotion() : MKT_Promotion::query()->find($id);
        if (! $row instanceof MKT_Promotion) {
            throw new \InvalidArgumentException('Акция не найдена.');
        }

        $row->fill([
            'title' => $title,
            'body' => $body,
            'is_active' => $isActive,
        ]);
        if ($imagePath !== null) {
            $row->image = $imagePath;
        }
        $row->save();

        return $this->mapper->toDomain($row);
    }
}
