<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Infrastructure\Content\Mapper\DeliveryConfigurationMapper;
use App\Infrastructure\Content\Model\DLV_Configuration;

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
