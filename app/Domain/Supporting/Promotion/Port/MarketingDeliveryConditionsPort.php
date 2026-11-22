<?php

namespace App\Domain\Supporting\Promotion\Port;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;

/**
 * Условия доставки для витрины Marketing. Marketing Domain не импортирует Company repo.
 */
interface MarketingDeliveryConditionsPort
{
    public function findActive(): ?DeliveryConditionsText;

    public function findCurrent(): ?DeliveryConditionsText;

    public function save(?int $id, string $body, bool $isActive): DeliveryConditionsText;
}
