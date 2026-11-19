<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;

final class GetAdminPromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly ProductPromotionRoleRepository $roles,
        private readonly PromotionZoneRepository $zones,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $policy = $this->policies->find();
        if ($policy === null) {
            throw new \InvalidArgumentException('Политика акций не найдена.');
        }

        return $this->presenter->adminPolicy(
            $policy,
            $this->roles->findAll(),
            $this->zones->findAll(),
        );
    }
}
