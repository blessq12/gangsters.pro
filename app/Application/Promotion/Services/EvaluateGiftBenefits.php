<?php

namespace App\Application\Promotion\Services;

use App\Domain\Promotion\Entity\PromotionPolicy;

final class EvaluateGiftBenefits
{
    /**
     * @return array<string, mixed>
     */
    public function buildBenefit(
        ?PromotionPolicy $promotionPolicy,
        string $orderChannel,
        int $currentKopecks,
    ): array {
        $rule = $promotionPolicy?->giftRuleForChannel($orderChannel);

        if (! is_array($rule) || ! ($rule['is_active'] ?? false)) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $thresholdKopecks = (int) $rule['min_order_amount_kopecks'];
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
     * @param  array<string, mixed>|null  $giftRule
     * @param  list<array<string, mixed>>  $giftCandidates
     * @return array<string, mixed>
     */
    public function buildPromotionState(
        ?array $giftRule,
        int $currentKopecks,
        ?int $selectedGiftProductId,
        array $giftCandidates,
    ): array {
        $candidateItems = [];
        $candidateProductIds = [];

        foreach ($giftCandidates as $candidate) {
            $productId = (int) ($candidate['id'] ?? $candidate['product_id'] ?? 0);
            $candidateProductIds[] = $productId;
            $candidateItems[] = [
                'id' => $productId,
                'name' => (string) ($candidate['name'] ?? $candidate['product_name'] ?? ''),
                'price_rub' => (int) ($candidate['price_rub'] ?? $candidate['price_rubles'] ?? 0),
                'image_url' => isset($candidate['image_url']) ? (string) $candidate['image_url'] : null,
                'composition' => is_array($candidate['composition'] ?? null)
                    ? $candidate['composition']
                    : [],
            ];
        }

        $eligible = is_array($giftRule)
            && (bool) ($giftRule['is_active'] ?? false)
            && $currentKopecks > (int) ($giftRule['min_order_amount_kopecks'] ?? 0)
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
