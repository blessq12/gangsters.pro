<?php

namespace App\Infrastructure\MarketingContent\Repository;

use App\Domain\MarketingContent\Entity\Banner;
use App\Domain\MarketingContent\Repository\BannerRepository;
use App\Infrastructure\MarketingContent\Mapper\BannerMapper;
use App\Infrastructure\MarketingContent\Model\MKT_Banner;

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
