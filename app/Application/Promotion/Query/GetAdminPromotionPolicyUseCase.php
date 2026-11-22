<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class GetAdminPromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly CatalogItemRepository $catalog,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId): array
    {
        $policy = $this->policies->findByBranch($branchId);
        if ($policy === null) {
            throw new \InvalidArgumentException('Политика акций филиала не найдена.');
        }

        $giftName = null;
        $giftProductId = $policy->giftProductId();
        if ($giftProductId !== null) {
            $names = $this->catalog->findProductNamesByIds([$giftProductId]);
            $giftName = $names[$giftProductId] ?? null;
        }

        return $this->presenter->adminPolicy($policy, $giftName);
    }
}
