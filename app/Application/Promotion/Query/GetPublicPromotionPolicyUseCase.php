<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;

final class GetPublicPromotionPolicyUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
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

        return $this->presenter->publicPolicy($policy);
    }
}
