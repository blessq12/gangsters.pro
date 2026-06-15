<?php

namespace App\Application\Promotion\useCases;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;

/**
 * Сценарий: публичный snapshot операционных правил акций.
 */
final class GetPromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        $policy = $this->promotionPolicies->find();

        return [
            'data' => $policy !== null ? $this->presenter->present($policy) : null,
        ];
    }
}
