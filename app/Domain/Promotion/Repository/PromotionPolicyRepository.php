<?php

namespace App\Domain\Promotion\Repository;

use App\Domain\Promotion\Entity\PromotionPolicy;

interface PromotionPolicyRepository
{
    public const SINGLETON_ID = 1;

    public function find(): ?PromotionPolicy;
}
