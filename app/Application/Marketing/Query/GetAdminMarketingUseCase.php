<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Marketing\Repository\BannerRepository;
use App\Domain\Marketing\Repository\PromotionRepository;

final class GetAdminMarketingUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly PromotionRepository $promotions,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return $this->presenter->present(
            banners: $this->banners->findAllOrdered(),
            promotions: $this->promotions->findAllOrdered(),
            contact: null,
            deliveryConditions: null,
        );
    }
}
