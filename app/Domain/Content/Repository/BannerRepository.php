<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Banner;

interface BannerRepository
{
    /**
     * @return list<Banner>
     */
    public function findActiveOrdered(): array;

    /**
     * @return list<Banner>
     */
    public function findAllOrdered(): array;

    public function save(?int $id, bool $isActive, ?string $desktopPath, ?string $mobilePath): Banner;
}
