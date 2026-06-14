<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Delivery\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Delivery\Model\DLV_Configuration;

final class EloquentDeliveryConfigurationRepository implements DeliveryConfigurationRepository
{
    public function __construct(
        private readonly DeliveryConfigurationMapper $mapper,
    ) {}

    public function findPublic(): ?DeliveryConfiguration
    {
        $row = DLV_Configuration::query()->find(self::SINGLETON_ID);

        return $row instanceof DLV_Configuration ? $this->mapper->toDomain($row) : null;
    }
}
