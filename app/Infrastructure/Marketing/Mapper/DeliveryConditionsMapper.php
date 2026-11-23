<?php

namespace App\Infrastructure\Marketing\Mapper;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;
use App\Infrastructure\Marketing\Model\MarketingDeliveryConditionsModel;

final class DeliveryConditionsMapper
{
    public function toDomain(MarketingDeliveryConditionsModel $row): DeliveryConditionsText
    {
        return new DeliveryConditionsText(
            id: (int) $row->id,
            body: (string) $row->body,
            isActive: (bool) $row->is_active,
        );
    }
}
