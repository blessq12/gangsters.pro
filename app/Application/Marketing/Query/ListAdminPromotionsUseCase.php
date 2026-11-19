<?php

namespace App\Application\Marketing\Query;

use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Promotion\Entity\Campaign;
use App\Domain\Supporting\Promotion\Repository\CampaignRepository;

final class ListAdminPromotionsUseCase
{
    public function __construct(
        private readonly CampaignRepository $promotions,
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
                fn (Campaign $promotion): array => $this->presenter->promotion($promotion),
                $this->promotions->listPage($offset, $limit),
            ),
            $this->promotions->countAll(),
            $offset,
            $limit,
        );
    }
}
