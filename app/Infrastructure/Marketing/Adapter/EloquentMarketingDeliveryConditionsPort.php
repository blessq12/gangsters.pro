<?php

namespace App\Infrastructure\Marketing\Adapter;

use App\Domain\Supporting\Company\Entity\DeliveryConditionsText;
use App\Domain\Supporting\Company\Repository\DeliveryConditionsTextRepository;
use App\Domain\Supporting\Marketing\Port\MarketingDeliveryConditionsPort;

final class EloquentMarketingDeliveryConditionsPort implements MarketingDeliveryConditionsPort
{
    public function __construct(
        private readonly DeliveryConditionsTextRepository $conditions,
    ) {}

    public function findActive(): ?DeliveryConditionsText
    {
        return $this->conditions->findActive();
    }

    public function findCurrent(): ?DeliveryConditionsText
    {
        return $this->conditions->findCurrent();
    }

    public function save(?int $id, string $body, bool $isActive): DeliveryConditionsText
    {
        return $this->conditions->save($id, $body, $isActive);
    }
}
