<?php

namespace App\Application\Marketing\Banner\Query;

use App\Application\Marketing\Banner\Presenter\AdminBannerPresenter;
use App\Domain\SystemContent\Repository\BannerRepository;

final class GetAdminBannerListQuery
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly AdminBannerPresenter $presenter,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            fn ($banner) => $this->presenter->presentListItem($banner),
            $this->banners->findAllOrdered(),
        );
    }
}
