<?php

namespace App\Infrastructure\Marketing\Mapper;

use App\Domain\Supporting\Marketing\Entity\Campaign;
use App\Infrastructure\Marketing\Model\PromotionMediaModel;
use App\Infrastructure\Marketing\Support\PublicMediaUrl;

final class PromotionMapper
{
    public function toDomain(PromotionMediaModel $row): Campaign
    {
        return new Campaign(
            id: (int) $row->id,
            title: (string) $row->title,
            body: $this->nullableString($row->body),
            image: PublicMediaUrl::resolve($this->nullableString($row->image)),
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
