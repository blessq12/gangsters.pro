<?php

namespace App\Application\Promotion\Services;

use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Port\CatalogComplementSetCandidate;
use App\Domain\Checkout\Port\CatalogGiftCandidate;
use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;

/**
 * Расчёт benefits_progress, delivery_pricing и promo_state на основе Promotion BC.
 */
final class EvaluatePromotionBenefits
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
    ) {}

    /**
     * @param  list<CatalogGiftCandidate>  $giftCandidates
     * @param  list<CatalogComplementSetCandidate>  $complementCandidates
     * @return array{
     *     benefits_progress: array<string, mixed>,
     *     delivery_pricing: array<string, mixed>|null,
     *     promo_state: array<string, mixed>
     * }
     */
    public function evaluate(
        int $currentKopecks,
        PromotionOrderChannel $orderChannel,
        ?DeliveryMethod $deliveryMethod,
        int $rollCount,
        ?int $selectedGiftProductId,
        array $giftCandidates,
        array $complementCandidates,
    ): array {
        $promotionPolicy = $this->promotionPolicies->find();
        $inZone = $this->resolveInZone();

        $giftBenefit = $this->buildGiftBenefit(
            promotionPolicy: $promotionPolicy,
            orderChannel: $orderChannel,
            currentKopecks: $currentKopecks,
        );

        $deliveryBenefit = $this->buildDeliveryBenefit(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
        );

        $deliveryFeeKopecks = $this->resolveDeliveryFeeKopecks(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
            inZone: $inZone,
        );

        $previewDeliveryFeeKopecks = $this->resolveDeliveryFeeKopecks(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $deliveryMethod ?? DeliveryMethod::Courier,
            currentKopecks: $currentKopecks,
            inZone: $inZone,
        );

        $giftPromotion = $this->buildGiftPromotionState(
            giftRule: $promotionPolicy?->giftRuleForChannel($orderChannel),
            currentKopecks: $currentKopecks,
            selectedGiftProductId: $selectedGiftProductId,
            giftCandidates: $giftCandidates,
        );

        $complementBenefit = $this->buildComplementBenefit(
            promotionPolicy: $promotionPolicy,
            rollCount: $rollCount,
            complementCandidates: $complementCandidates,
        );

        $complementPromotion = $this->buildComplementPromotionState(
            complementRule: $promotionPolicy?->complementSetBenefitRule(),
            rollCount: $rollCount,
            complementCandidates: $complementCandidates,
        );

        $deliveryPricing = $this->buildDeliveryPricing(
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
            deliveryFeeKopecks: $deliveryMethod === null
                ? $previewDeliveryFeeKopecks
                : $deliveryFeeKopecks,
            promotionPolicy: $promotionPolicy,
        );

        return [
            'benefits_progress' => [
                'delivery' => $deliveryBenefit,
                'gift' => $giftBenefit,
                'complement' => $complementBenefit,
            ],
            'delivery_pricing' => $deliveryPricing,
            'promo_state' => [
                'gift_promotion' => $giftPromotion,
                'complement_promotion' => $complementPromotion,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGiftBenefit(
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
     * @return array<string, mixed>
     */
    private function buildDeliveryBenefit(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
    ): array {
        if ($deliveryMethod === DeliveryMethod::Pickup) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $policy = $promotionPolicy?->deliveryBenefitPolicy();
        if ($policy === null || ! $policy->isActive()) {
            return $this->inactiveBenefit($currentKopecks);
        }

        $thresholdKopecks = $policy->freeDeliveryThresholdKopecks();
        $isReached = $currentKopecks >= $thresholdKopecks;
        $remainingKopecks = $isReached
            ? 0
            : max(0, $thresholdKopecks - $currentKopecks);

        $benefit = [
            'isActive' => true,
            'isReached' => $isReached,
            'thresholdKopecks' => $thresholdKopecks,
            'currentKopecks' => $currentKopecks,
            'remainingKopecks' => $remainingKopecks,
        ];

        if ($deliveryMethod === null) {
            $benefit['isPreview'] = true;
        }

        return $benefit;
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

    private function resolveInZone(): ?bool
    {
        $configuration = $this->deliveryConfigurations->findPublic();

        if (! $configuration instanceof DeliveryConfiguration) {
            return null;
        }

        return null;
    }

    private function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int {
        if ($deliveryMethod === DeliveryMethod::Pickup) {
            return 0;
        }

        if ($deliveryMethod !== DeliveryMethod::Courier) {
            return 0;
        }

        $deliveryConfiguration = $this->deliveryConfigurations->findPublic();
        $baseInZoneFee = $deliveryConfiguration?->deliveryFeeKopecks() ?? 0;
        $baseOutsideZoneFee = $deliveryConfiguration?->outsideZoneDeliveryFeeKopecks() ?? $baseInZoneFee;

        $policy = $promotionPolicy?->deliveryBenefitPolicy();
        if ($policy === null || ! $policy->isActive()) {
            return $inZone === false ? $baseOutsideZoneFee : $baseInZoneFee;
        }

        $thresholdKopecks = $policy->freeDeliveryThresholdKopecks();

        if ($currentKopecks < $thresholdKopecks) {
            return $inZone === false ? $baseOutsideZoneFee : $baseInZoneFee;
        }

        if (
            $inZone === false
            && $policy->outsideZoneAtThresholdFeeMode() === DeliveryFeeMode::BasePlusSurcharge
        ) {
            return $baseInZoneFee + $policy->outsideZoneSurchargeKopecks();
        }

        if ($policy->inZoneAtThresholdFeeMode() === DeliveryFeeMode::Free) {
            return 0;
        }

        return $baseInZoneFee;
    }

    /**
     * @param  list<CatalogGiftCandidate>  $giftCandidates
     * @return array<string, mixed>
     */
    private function buildGiftPromotionState(
        ?GiftBenefitRule $giftRule,
        int $currentKopecks,
        ?int $selectedGiftProductId,
        array $giftCandidates,
    ): array {
        $candidateItems = array_map(
            static fn (CatalogGiftCandidate $candidate): array => [
                'id' => $candidate->productId(),
                'name' => $candidate->productName(),
                'price_rub' => $candidate->priceRubles(),
                'image_url' => $candidate->imageUrl(),
            ],
            $giftCandidates,
        );

        $candidateProductIds = array_map(
            static fn (CatalogGiftCandidate $candidate): int => $candidate->productId(),
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
     * @param  list<CatalogComplementSetCandidate>  $complementCandidates
     * @return array<string, mixed>
     */
    private function buildComplementBenefit(
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
            return $this->inactiveComplementBenefit($rollCount);
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
     * @return array<string, mixed>
     */
    private function inactiveComplementBenefit(int $rollCount): array
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

    /**
     * @param  list<CatalogComplementSetCandidate>  $complementCandidates
     * @return array<string, mixed>
     */
    private function buildComplementPromotionState(
        ?ComplementSetBenefitRule $complementRule,
        int $rollCount,
        array $complementCandidates,
    ): array {
        $candidateItems = array_map(
            static fn (CatalogComplementSetCandidate $candidate): array => [
                'id' => $candidate->productId(),
                'name' => $candidate->productName(),
                'price_rub' => $candidate->priceRubles(),
                'image_url' => $candidate->imageUrl(),
            ],
            $complementCandidates,
        );

        $candidateProductIds = array_map(
            static fn (CatalogComplementSetCandidate $candidate): int => $candidate->productId(),
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
     * @return array<string, mixed>|null
     */
    private function buildDeliveryPricing(
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        int $deliveryFeeKopecks,
        ?PromotionPolicy $promotionPolicy,
    ): ?array {
        $isPreview = $deliveryMethod === null;
        $effectiveMethod = $deliveryMethod ?? DeliveryMethod::Courier;

        if (! $isPreview && $effectiveMethod === DeliveryMethod::Pickup) {
            $method = $effectiveMethod->value;
            $grandTotalKopecks = $currentKopecks;

            return [
                'method' => $method,
                'items_payable_kopecks' => $currentKopecks,
                'delivery_fee_kopecks' => 0,
                'is_free' => true,
                'remaining_to_free_kopecks' => 0,
                'items_total_kopecks' => $currentKopecks,
                'grand_total_kopecks' => $grandTotalKopecks,
                'items_total_rub' => $currentKopecks / 100,
                'delivery_fee_rub' => 0,
                'grand_total_rub' => $grandTotalKopecks / 100,
                'is_preview' => false,
            ];
        }

        $method = $effectiveMethod->value;
        $grandTotalKopecks = $currentKopecks + $deliveryFeeKopecks;
        $freeThresholdKopecks = $promotionPolicy?->deliveryBenefitPolicy()->freeDeliveryThresholdKopecks();
        $remainingToFreeKopecks = 0;

        if (
            $effectiveMethod === DeliveryMethod::Courier
            && is_int($freeThresholdKopecks)
            && $currentKopecks < $freeThresholdKopecks
        ) {
            $remainingToFreeKopecks = $freeThresholdKopecks - $currentKopecks;
        }

        return [
            'method' => $method,
            'items_payable_kopecks' => $currentKopecks,
            'delivery_fee_kopecks' => $deliveryFeeKopecks,
            'is_free' => $effectiveMethod === DeliveryMethod::Courier && $deliveryFeeKopecks === 0,
            'remaining_to_free_kopecks' => max(0, $remainingToFreeKopecks),
            'items_total_kopecks' => $currentKopecks,
            'grand_total_kopecks' => $grandTotalKopecks,
            'items_total_rub' => $currentKopecks / 100,
            'delivery_fee_rub' => $deliveryFeeKopecks / 100,
            'grand_total_rub' => $grandTotalKopecks / 100,
            'is_preview' => $isPreview,
        ];
    }
}
