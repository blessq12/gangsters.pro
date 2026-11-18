<?php

namespace App\Infrastructure\Marketing\Mapper;

use App\Domain\Marketing\Entity\Banner;
use App\Infrastructure\Marketing\Model\MKT_Banner;
use App\Infrastructure\Marketing\Support\PublicMediaUrl;

final class BannerMapper
{
    public function toDomain(MKT_Banner $row): Banner
    {
        return new Banner(
            id: (int) $row->id,
            imageDesktop: PublicMediaUrl::resolve($this->nullableString($row->image_desktop)),
            imageMobile: PublicMediaUrl::resolve($this->nullableString($row->image_mobile)),
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
