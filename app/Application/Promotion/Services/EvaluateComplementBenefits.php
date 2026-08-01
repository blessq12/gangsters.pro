<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Entity\PromotionPolicy;

final class EvaluateComplementBenefits
{
    /**
     * @param  list<array<string, mixed>>  $complementCandidates
     * @return array<string, mixed>
     */
    public function buildBenefit(
        ?PromotionPolicy $promotionPolicy,
        int $rollCount,
        array $complementCandidates,
    ): array {
        $rule = $promotionPolicy?->complementSetBenefit();

        if (
            ! is_array($rule)
            || ! ($rule['is_active'] ?? false)
            || $complementCandidates === []
        ) {
            return $this->inactiveBenefit($rollCount);
        }

        $rollsPerSet = (int) $rule['rolls_per_set'];
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
     * @param  array{rolls_per_set: int, is_active: bool}|null  $complementRule
     * @param  list<array<string, mixed>>  $complementCandidates
     * @return array<string, mixed>
     */
    public function buildPromotionState(
        ?array $complementRule,
        int $rollCount,
        array $complementCandidates,
    ): array {
        $candidateItems = [];
        $candidateProductIds = [];

        foreach ($complementCandidates as $candidate) {
            $productId = (int) ($candidate['id'] ?? $candidate['product_id'] ?? 0);
            $candidateProductIds[] = $productId;
            $candidateItems[] = [
                'id' => $productId,
                'name' => (string) ($candidate['name'] ?? $candidate['product_name'] ?? ''),
                'price_rub' => (int) ($candidate['price_rub'] ?? $candidate['price_rubles'] ?? 0),
                'image_url' => isset($candidate['image_url']) ? (string) $candidate['image_url'] : null,
            ];
        }

        $rollsPerSet = isset($complementRule['rolls_per_set'])
            ? (int) $complementRule['rolls_per_set']
            : null;
        $entitledSetCount = 0;
        $remainingRollCount = 0;

        if (
            is_array($complementRule)
            && (bool) ($complementRule['is_active'] ?? false)
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
