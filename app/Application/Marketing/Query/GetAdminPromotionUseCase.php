<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Supporting\Promotion\Repository\CampaignRepository;

final class GetAdminPromotionUseCase
{
    public function __construct(
        private readonly CampaignRepository $promotions,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $promotion = $this->promotions->findById($id);
        if ($promotion === null) {
            throw new \InvalidArgumentException('Акция не найдена.');
        }

        return $this->presenter->promotion($promotion);
    }
}
