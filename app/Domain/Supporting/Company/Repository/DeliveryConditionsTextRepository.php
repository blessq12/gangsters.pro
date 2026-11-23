<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;

interface DeliveryConditionsTextRepository
{
    public function findCurrent(): ?DeliveryConditionsText;

    public function findActive(): ?DeliveryConditionsText;

    public function save(?int $id, string $body, bool $isActive): DeliveryConditionsText;
}
