<?php

namespace App\Infrastructure\MarketingContent\Mapper;

use App\Domain\MarketingContent\Entity\Banner;
use App\Infrastructure\MarketingContent\Model\MKT_Banner;

final class BannerMapper
{
    public function toDomain(MKT_Banner $row): Banner
    {
        return new Banner(
            id: (int) $row->id,
            imageDesktop: $this->nullableString($row->image_desktop),
            imageMobile: $this->nullableString($row->image_mobile),
            sortOrder: (int) ($row->sort_order ?? 0),
            isActive: (bool) $row->is_active,
        );
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
