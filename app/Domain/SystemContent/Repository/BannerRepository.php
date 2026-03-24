<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Banner;

interface BannerRepository
{
    /**
     * @return Banner[]
     */
    public function findAllOrdered(): array;
}

