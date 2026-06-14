<?php

namespace App\Infrastructure\MarketingContent\Mapper;

use App\Domain\MarketingContent\Entity\Promotion;
use App\Infrastructure\MarketingContent\Model\MKT_Promotion;

final class PromotionMapper
{
    public function toDomain(MKT_Promotion $row): Promotion
    {
        return new Promotion(
            id: (int) $row->id,
            title: (string) $row->title,
            body: $this->nullableString($row->body),
            image: $this->nullableString($row->image),
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
