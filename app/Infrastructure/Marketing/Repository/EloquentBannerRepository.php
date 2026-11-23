<?php

namespace App\Infrastructure\Marketing\Repository;

use App\Domain\Supporting\Marketing\Entity\Banner;
use App\Domain\Supporting\Marketing\Repository\BannerRepository;
use App\Infrastructure\Marketing\Mapper\BannerMapper;
use App\Infrastructure\Marketing\Model\BannerModel;

final class EloquentBannerRepository implements BannerRepository
{
    public function __construct(
        private readonly BannerMapper $mapper,
    ) {}

    public function findActiveOrdered(): array
    {
        return BannerModel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (BannerModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return BannerModel::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (BannerModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPage(int $offset, int $limit): array
    {
        return BannerModel::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (BannerModel $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) BannerModel::query()->count();
    }

    public function findById(int $id): ?Banner
    {
        $row = BannerModel::query()->find($id);

        return $row instanceof BannerModel ? $this->mapper->toDomain($row) : null;
    }

    public function findStoredPaths(int $id): ?array
    {
        $row = BannerModel::query()->find($id);
        if (! $row instanceof BannerModel) {
            return null;
        }

        return [
            'image_desktop' => $this->nullableString($row->image_desktop),
            'image_mobile' => $this->nullableString($row->image_mobile),
        ];
    }

    public function save(?int $id, bool $isActive, ?string $desktopPath, ?string $mobilePath): Banner
    {
        $row = $id === null ? new BannerModel() : BannerModel::query()->find($id);
        if (! $row instanceof BannerModel) {
            throw new \InvalidArgumentException('Баннер не найден.');
        }

        if ($id === null && ($desktopPath === null || $mobilePath === null)) {
            throw new \InvalidArgumentException('Нужны картинки для десктопа и телефона.');
        }

        $row->is_active = $isActive;
        if ($desktopPath !== null) {
            $row->image_desktop = $desktopPath;
        }
        if ($mobilePath !== null) {
            $row->image_mobile = $mobilePath;
        }
        $row->save();

        return $this->mapper->toDomain($row);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
