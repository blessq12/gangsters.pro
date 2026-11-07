<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\Banner;
use App\Domain\Content\Repository\BannerRepository;
use App\Infrastructure\Content\Mapper\BannerMapper;
use App\Infrastructure\Content\Model\MKT_Banner;

final class EloquentBannerRepository implements BannerRepository
{
    public function __construct(
        private readonly BannerMapper $mapper,
    ) {}

    public function findActiveOrdered(): array
    {
        return MKT_Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MKT_Banner $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllOrdered(): array
    {
        return MKT_Banner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (MKT_Banner $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(?int $id, bool $isActive, ?string $desktopPath, ?string $mobilePath): Banner
    {
        $row = $id === null ? new MKT_Banner() : MKT_Banner::query()->find($id);
        if (! $row instanceof MKT_Banner) {
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
}
