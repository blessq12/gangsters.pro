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
}
