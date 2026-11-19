<?php

namespace App\Domain\Supporting\Promotion\Repository;

use App\Domain\Supporting\Promotion\Entity\Banner;

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
