<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\BenefitProductCandidate;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;

/**
 * Расчёт gift-бенефита и promo_state для подарка.
 */
final class EvaluateGiftBenefits
{
    /**
     * @return array<string, mixed>
     */
    public function buildBenefit(
        ?PromotionPolicy $promotionPolicy,
        PromotionOrderChannel $orderChannel,
        int $currentKopecks,
    ): array {
        $rule = $promotionPolicy?->giftRuleForChannel($orderChannel);

        if (! $rule instanceof GiftBenefitRule || ! $rule->isActive()) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $thresholdKopecks = $rule->minOrderAmountKopecks();
        $isReached = $currentKopecks > $thresholdKopecks;
        $remainingKopecks = $isReached
            ? 0
            : max(0, $thresholdKopecks + 1 - $currentKopecks);

        return [
            'isActive' => true,
            'isReached' => $isReached,
            'thresholdKopecks' => $thresholdKopecks,
            'currentKopecks' => $currentKopecks,
            'remainingKopecks' => $remainingKopecks,
        ];
    }

    /**
     * @param  list<BenefitProductCandidate>  $giftCandidates
     * @return array<string, mixed>
     */
    public function buildPromotionState(
        ?GiftBenefitRule $giftRule,
        int $currentKopecks,
        ?int $selectedGiftProductId,
        array $giftCandidates,
    ): array {
        $candidateItems = array_map(
            static fn (BenefitProductCandidate $candidate): array => [
                'id' => $candidate->productId(),
                'name' => $candidate->productName(),
                'price_rub' => $candidate->priceRubles(),
                'image_url' => $candidate->imageUrl(),
            ],
            $giftCandidates,
        );

        $candidateProductIds = array_map(
            static fn (BenefitProductCandidate $candidate): int => $candidate->productId(),
            $giftCandidates,
        );

        $eligible = $giftRule instanceof GiftBenefitRule
            && $giftRule->isActive()
            && $currentKopecks > $giftRule->minOrderAmountKopecks()
            && $candidateProductIds !== [];

        $phase = 'below_threshold';
        if ($eligible) {
            $phase = $selectedGiftProductId !== null ? 'selected' : 'select_gift';
        }

        return [
            'eligible' => $eligible,
            'phase' => $phase,
            'selected_product_id' => $selectedGiftProductId,
            'candidate_product_ids' => $candidateProductIds,
            'candidate_items' => $candidateItems,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inactiveBenefit(int $currentKopecks): array
    {
        return [
            'isActive' => false,
            'isReached' => false,
            'thresholdKopecks' => null,
            'currentKopecks' => $currentKopecks,
            'remainingKopecks' => 0,
        ];
    }
}
