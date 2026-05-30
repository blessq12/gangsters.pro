<?php

namespace App\Application\Marketing\Banner\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Banner\Presenter\AdminBannerPresenter;
use App\Domain\SystemContent\Repository\BannerRepository;

final class GetAdminBannerDetailQuery
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly AdminBannerPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $banner = $this->banners->findById($id);
        if ($banner === null) {
            throw new ApiException('Banner not found.', 404);
        }

        return $this->presenter->presentDetail($banner);
    }
}
