<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\ValueObject\BenefitProductCandidate;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;

/**
 * Расчёт complement-бенефита и promo_state для комплекта дополнений.
 */
final class EvaluateComplementBenefits
{
    /**
     * @param  list<BenefitProductCandidate>  $complementCandidates
     * @return array<string, mixed>
     */
    public function buildBenefit(
        ?PromotionPolicy $promotionPolicy,
        int $rollCount,
        array $complementCandidates,
    ): array {
        $rule = $promotionPolicy?->complementSetBenefitRule();

        if (
            ! $rule instanceof ComplementSetBenefitRule
            || ! $rule->isActive()
            || $complementCandidates === []
        ) {
            return $this->inactiveBenefit($rollCount);
        }

        $rollsPerSet = $rule->rollsPerSet();
        $entitledSetCount = intdiv($rollCount, $rollsPerSet);
        $rollsTowardNextSet = $rollCount % $rollsPerSet;
        $remainingRollCount = $entitledSetCount > 0 && $rollsTowardNextSet === 0
            ? 0
            : $rollsPerSet - $rollsTowardNextSet;

        return [
            'isActive' => true,
            'isReached' => $entitledSetCount > 0,
            'rollsPerSet' => $rollsPerSet,
            'currentRollCount' => $rollCount,
            'entitledSetCount' => $entitledSetCount,
            'remainingRollCount' => $remainingRollCount,
        ];
    }

    /**
     * @param  list<BenefitProductCandidate>  $complementCandidates
     * @return array<string, mixed>
     */
    public function buildPromotionState(
        ?ComplementSetBenefitRule $complementRule,
        int $rollCount,
        array $complementCandidates,
    ): array {
        $candidateItems = array_map(
            static fn (BenefitProductCandidate $candidate): array => [
                'id' => $candidate->productId(),
                'name' => $candidate->productName(),
                'price_rub' => $candidate->priceRubles(),
                'image_url' => $candidate->imageUrl(),
            ],
            $complementCandidates,
        );

        $candidateProductIds = array_map(
            static fn (BenefitProductCandidate $candidate): int => $candidate->productId(),
            $complementCandidates,
        );

        $rollsPerSet = $complementRule?->rollsPerSet();
        $entitledSetCount = 0;
        $remainingRollCount = 0;

        if (
            $complementRule instanceof ComplementSetBenefitRule
            && $complementRule->isActive()
            && is_int($rollsPerSet)
            && $rollsPerSet > 0
            && $candidateProductIds !== []
        ) {
            $entitledSetCount = intdiv($rollCount, $rollsPerSet);
            $rollsTowardNextSet = $rollCount % $rollsPerSet;
            $remainingRollCount = $entitledSetCount > 0 && $rollsTowardNextSet === 0
                ? 0
                : $rollsPerSet - $rollsTowardNextSet;
        }

        $eligible = $entitledSetCount > 0;

        return [
            'eligible' => $eligible,
            'phase' => $eligible ? 'entitled' : 'below_threshold',
            'rolls_per_set' => $rollsPerSet,
            'roll_count' => $rollCount,
            'entitled_set_count' => $entitledSetCount,
            'remaining_roll_count' => $remainingRollCount,
            'candidate_product_ids' => $candidateProductIds,
            'candidate_items' => $candidateItems,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inactiveBenefit(int $rollCount): array
    {
        return [
            'isActive' => false,
            'isReached' => false,
            'rollsPerSet' => null,
            'currentRollCount' => $rollCount,
            'entitledSetCount' => 0,
            'remainingRollCount' => 0,
        ];
    }
}
