<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Marketing\Entity\Banner;
use App\Domain\Supporting\Marketing\Repository\BannerRepository;

final class ListAdminBannersUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            array_map(
                fn (Banner $banner): array => $this->presenter->banner($banner),
                $this->banners->listPage($offset, $limit),
            ),
            $this->banners->countAll(),
            $offset,
            $limit,
        );
    }
}
