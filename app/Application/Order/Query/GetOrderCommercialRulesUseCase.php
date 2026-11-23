<?php

namespace App\Application\Order\Query;

use App\Application\Order\Presenter\OrderCommercialRulesPresenter;
use App\Domain\Core\Order\Repository\OrderCommercialRulesRepository;

final class GetOrderCommercialRulesUseCase
{
    public function __construct(
        private readonly OrderCommercialRulesRepository $commercialRules,
        private readonly OrderCommercialRulesPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId): array
    {
        $rules = $this->commercialRules->findByBranch($branchId);
        if ($rules === null) {
            throw new \InvalidArgumentException('Коммерческие правила филиала не найдены.');
        }

        return $this->presenter->present($rules);
    }
}
