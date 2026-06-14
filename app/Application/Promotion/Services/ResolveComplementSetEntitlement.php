<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;

/**
 * Сколько комплектов дополнений положено по правилам Promotion BC.
 */
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

        $rule = $this->promotionPolicies->find()?->complementSetBenefitRule();

        if (! $rule instanceof ComplementSetBenefitRule || ! $rule->isActive()) {
            return 0;
        }

        if ($rollCount < $rule->rollsPerSet()) {
            return 0;
        }

        return intdiv($rollCount, $rule->rollsPerSet());
    }
}
