<?php

namespace App\Application\Marketing\Promotion\Query;

use App\Application\Marketing\Promotion\Presenter\AdminPromotionPresenter;
use App\Domain\SystemContent\Repository\PromotionRepository;

final class GetAdminPromotionListQuery
{
    public function __construct(
        private readonly PromotionRepository $promotions,
        private readonly AdminPromotionPresenter $presenter,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            fn ($promotion) => $this->presenter->presentListItem($promotion),
            $this->promotions->findAllOrdered(),
        );
    }
}
