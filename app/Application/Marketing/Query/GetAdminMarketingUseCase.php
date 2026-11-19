<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Company\Repository\DeliveryConditionsTextRepository;
use App\Domain\Supporting\Promotion\Repository\BannerRepository;
use App\Domain\Supporting\Promotion\Repository\CampaignRepository;

final class GetAdminMarketingUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly CampaignRepository $promotions,
        private readonly DeliveryConditionsTextRepository $deliveryConditions,
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
            deliveryConditions: $this->deliveryConditions->findCurrent(),
        );
    }
}
