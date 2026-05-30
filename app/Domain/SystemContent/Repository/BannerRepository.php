<?php

namespace App\Domain\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Banner;

interface BannerRepository
{
    /**
     * @return Banner[]
     */
    public function findAllOrdered(): array;

    public function findById(int $id): ?Banner;

    public function save(Banner $banner): Banner;

    public function delete(int $id): void;
}

