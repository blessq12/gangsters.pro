<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Marketing\Repository\BannerRepository;

final class GetAdminBannerUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $banner = $this->banners->findById($id);
        if ($banner === null) {
            throw new \InvalidArgumentException('Баннер не найден.');
        }

        return $this->presenter->banner($banner);
    }
}
