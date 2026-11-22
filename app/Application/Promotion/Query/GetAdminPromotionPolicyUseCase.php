<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;

final class GetAdminPromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
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

        return $this->presenter->adminPolicy($policy);
    }
}
