<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Supporting\Marketing\Entity\Campaign;
use App\Domain\Supporting\Marketing\Repository\CampaignRepository;
use App\Infrastructure\Marketing\Mapper\PromotionMapper;
use App\Infrastructure\Marketing\Model\PromotionMediaModel;

final class EloquentPromotionRepository implements CampaignRepository
{
    public function __construct(
        private readonly PromotionMapper $mapper,
    ) {}

    public function findActiveOrdered(): array
    {
        return PromotionMediaModel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PromotionMediaModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return PromotionMediaModel::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PromotionMediaModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPage(int $offset, int $limit): array
    {
        return PromotionMediaModel::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (PromotionMediaModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) PromotionMediaModel::query()->count();
    }

    public function findById(int $id): ?Campaign
    {
        $row = PromotionMediaModel::query()->find($id);

        return $row instanceof PromotionMediaModel ? $this->mapper->toDomain($row) : null;
    }

    public function findStoredImagePath(int $id): ?string
    {
        $row = PromotionMediaModel::query()->find($id);
        if (! $row instanceof PromotionMediaModel) {
            return null;
        }

        $path = trim((string) ($row->image ?? ''));

        return $path !== '' ? $path : null;
    }

    public function save(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): Campaign
    {
        $row = $id === null ? new PromotionMediaModel : PromotionMediaModel::query()->find($id);
        if (! $row instanceof PromotionMediaModel) {
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
