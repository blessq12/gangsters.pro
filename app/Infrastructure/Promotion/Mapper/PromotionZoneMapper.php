<?php

namespace App\Infrastructure\Promotion\Mapper;

use App\Domain\Promotion\Entity\PromotionZone;
use App\Infrastructure\Promotion\Model\PRM_Zone;

final class PromotionZoneMapper
{
    public function toDomain(PRM_Zone $row): PromotionZone
    {
        return new PromotionZone(
            id: (string) $row->id,
            name: (string) $row->name,
            feeKopecks: max(0, (int) $row->fee_kopecks),
            isRemote: (bool) $row->is_remote,
        );
    }
}
