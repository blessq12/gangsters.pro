<?php

namespace App\Domain\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryConfiguration;

interface DeliveryConfigurationRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?DeliveryConfiguration;
}
