<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Banner;

interface BannerRepository
{
    /**
     * @return list<Banner>
     */
    public function findActiveOrdered(): array;
}
