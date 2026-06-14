<?php

namespace App\Domain\MarketingContent\Repository;

use App\Domain\MarketingContent\Entity\Banner;

interface BannerRepository
{
    /**
     * @return list<Banner>
     */
    public function findActiveOrdered(): array;
}
