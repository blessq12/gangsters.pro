<?php

namespace App\Application\Marketing\Banner\Command;

use App\Domain\SystemContent\Repository\BannerRepository;

final class DeleteBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
    ) {
    }

    public function execute(int $id): void
    {
        $this->banners->delete($id);
    }
}
