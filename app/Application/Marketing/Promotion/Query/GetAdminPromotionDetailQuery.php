<?php

namespace App\Application\Marketing\Promotion\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Marketing\Promotion\Presenter\AdminPromotionPresenter;
use App\Domain\SystemContent\Repository\PromotionRepository;

final class GetAdminPromotionDetailQuery
{
    public function __construct(
        private readonly PromotionRepository $promotions,
        private readonly AdminPromotionPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $promotion = $this->promotions->findById($id);
        if ($promotion === null) {
            throw new ApiException('Promotion not found.', 404);
        }

        return $this->presenter->presentDetail($promotion);
    }
}
