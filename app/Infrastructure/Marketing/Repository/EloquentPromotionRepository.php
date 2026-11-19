<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Supporting\Promotion\Entity\Campaign;
use App\Domain\Supporting\Promotion\Repository\CampaignRepository;
use App\Infrastructure\Marketing\Mapper\PromotionMapper;
use App\Infrastructure\Marketing\Model\MKT_Promotion;

final class EloquentPromotionRepository implements CampaignRepository
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

    public function listPage(int $offset, int $limit): array
    {
        return MKT_Promotion::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (MKT_Promotion $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) MKT_Promotion::query()->count();
    }

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Campaign
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
