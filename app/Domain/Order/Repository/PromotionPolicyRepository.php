<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\PromotionPolicy;

interface PromotionPolicyRepository
{
    public const SINGLETON_ID = 1;

    public function find(): ?PromotionPolicy;
}
