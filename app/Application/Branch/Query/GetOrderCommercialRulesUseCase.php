<?php

namespace App\Application\Branch\Query;

use App\Application\Branch\Presenter\OrderCommercialRulesPresenter;
use App\Domain\Supporting\Branch\Repository\BranchCommercialRulesRepository;

final class GetOrderCommercialRulesUseCase
{
    public function __construct(
        private readonly BranchCommercialRulesRepository $commercialRules,
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
