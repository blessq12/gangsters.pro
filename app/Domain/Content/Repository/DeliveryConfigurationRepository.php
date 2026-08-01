<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\DeliveryConfiguration;

interface DeliveryConfigurationRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?DeliveryConfiguration;
}
