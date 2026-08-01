<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Repository\PromotionPolicyRepository;

final class ResolveComplementSetEntitlement
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
    ) {}

    public function resolve(int $rollCount, bool $hasCandidates): int
    {
        if (! $hasCandidates) {
            return 0;
        }

        $rule = $this->promotionPolicies->find()?->complementSetBenefit();

        if (! is_array($rule) || ! ($rule['is_active'] ?? false)) {
            return 0;
        }

        $rollsPerSet = (int) ($rule['rolls_per_set'] ?? 0);
        if ($rollCount < $rollsPerSet || $rollsPerSet < 1) {
            return 0;
        }

        return intdiv($rollCount, $rollsPerSet);
    }
}
