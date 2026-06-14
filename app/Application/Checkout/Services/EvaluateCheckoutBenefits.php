<?php

namespace App\Application\Checkout\Services;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Port\CatalogComplementSetCandidate;
use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogGiftCandidate;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Checkout\Port\CatalogRollMetaPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;

/**
 * Расчёт benefits_progress, delivery_pricing и promo_state для ответа Checkout API.
 */
final class EvaluateCheckoutBenefits
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
        private readonly CatalogGiftCandidatesPort $giftCandidates,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CatalogRollMetaPort $rollMeta,
    ) {}

    /**
     * @return array{
     *     benefits_progress: array<string, mixed>,
     *     delivery_pricing: array<string, mixed>|null,
     *     promo_state: array<string, mixed>
     * }
     */
    public function evaluate(Checkout $checkout): array
    {
        $currentKopecks = $this->payableTotalKopecks($checkout);
        $orderChannel = $this->resolveOrderChannel($checkout);
        $deliveryMethod = $checkout->delivery()?->method();
        $promotionPolicy = $this->promotionPolicies->find();
        $deliveryConfiguration = $this->deliveryConfigurations->findPublic();
        $giftCandidates = $this->giftCandidates->listActiveGiftCandidates();
        $complementSetCandidates = $this->complementCandidates->listActiveComplementSetCandidates();
        $selectedGiftProductId = $this->resolveSelectedGiftProductId($checkout);
        $rollCount = $this->countRollUnitsInCart($checkout);

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
            deliveryConfiguration: $deliveryConfiguration,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
            inZone: $this->resolveInZone($deliveryConfiguration),
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
            complementCandidates: $complementSetCandidates,
        );

        $complementPromotion = $this->buildComplementPromotionState(
            complementRule: $promotionPolicy?->complementSetBenefitRule(),
            rollCount: $rollCount,
            complementCandidates: $complementSetCandidates,
        );

        $deliveryPricing = $this->buildDeliveryPricing(
            deliveryMethod: $deliveryMethod,
            currentKopecks: $currentKopecks,
            deliveryFeeKopecks: $deliveryFeeKopecks,
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

    private function countRollUnitsInCart(Checkout $checkout): int
    {
        $userLines = array_values(array_filter(
            $checkout->cart()->lines(),
            fn (CartLineSnapshot $line): bool => ! $this->isPromotionSystemLine($line),
        ));

        if ($userLines === []) {
            return 0;
        }

        $productIds = array_map(
            static fn (CartLineSnapshot $line): int => $line->productId(),
            $userLines,
        );

        $countsAsRollByProductId = $this->rollMeta->countsAsRollByProductIds($productIds);
        $rollCount = 0;

        foreach ($userLines as $line) {
            if (! ($countsAsRollByProductId[$line->productId()] ?? false)) {
                continue;
            }

            $rollCount += $line->quantity();
        }

        return $rollCount;
    }

    private function payableTotalKopecks(Checkout $checkout): int
    {
        $totalRubles = 0;

        foreach ($checkout->cart()->lines() as $line) {
            if ($this->isPromotionSystemLine($line)) {
                continue;
            }

            $totalRubles += $line->lineTotal()->amountRubles();
        }

        return $totalRubles * 100;
    }

    private function isGiftLine(CartLineSnapshot $line): bool
    {
        $payload = $line->payload();

        return is_array($payload) && (($payload['kind'] ?? null) === 'gift');
    }

    private function isComplementLine(CartLineSnapshot $line): bool
    {
        $payload = $line->payload();

        return is_array($payload) && (($payload['kind'] ?? null) === 'complement');
    }

    private function isPromotionSystemLine(CartLineSnapshot $line): bool
    {
        return $this->isGiftLine($line) || $this->isComplementLine($line);
    }

    private function resolveOrderChannel(Checkout $checkout): PromotionOrderChannel
    {
        $method = $checkout->delivery()?->method();

        if ($method === DeliveryMethod::Courier) {
            return PromotionOrderChannel::Courier;
        }

        return PromotionOrderChannel::Pickup;
    }

    private function resolveSelectedGiftProductId(Checkout $checkout): ?int
    {
        foreach ($checkout->cart()->lines() as $line) {
            if (! $this->isGiftLine($line)) {
                continue;
            }

            return $line->productId();
        }

        return null;
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

    private function resolveInZone(?DeliveryConfiguration $deliveryConfiguration): ?bool
    {
        if (! $deliveryConfiguration instanceof DeliveryConfiguration) {
            return null;
        }

        // Координаты адреса клиента в checkout пока не хранятся — зону определить нельзя.
        return null;
    }

    private function resolveDeliveryFeeKopecks(
        ?PromotionPolicy $promotionPolicy,
        ?DeliveryConfiguration $deliveryConfiguration,
        ?DeliveryMethod $deliveryMethod,
        int $currentKopecks,
        ?bool $inZone,
    ): int {
        if ($deliveryMethod !== DeliveryMethod::Courier) {
            return 0;
        }

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

        if ($inZone === true && $policy->inZoneAtThresholdFeeMode() === DeliveryFeeMode::Free) {
            return 0;
        }

        if (
            $inZone === false
            && $policy->outsideZoneAtThresholdFeeMode() === DeliveryFeeMode::BasePlusSurcharge
        ) {
            return $baseInZoneFee + $policy->outsideZoneSurchargeKopecks();
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
        if ($deliveryMethod === null) {
            return null;
        }

        $method = $deliveryMethod->value;
        $grandTotalKopecks = $currentKopecks + $deliveryFeeKopecks;
        $freeThresholdKopecks = $promotionPolicy?->deliveryBenefitPolicy()->freeDeliveryThresholdKopecks();
        $remainingToFreeKopecks = 0;

        if (
            $deliveryMethod === DeliveryMethod::Courier
            && is_int($freeThresholdKopecks)
            && $currentKopecks < $freeThresholdKopecks
        ) {
            $remainingToFreeKopecks = $freeThresholdKopecks - $currentKopecks;
        }

        return [
            'method' => $method,
            'items_payable_kopecks' => $currentKopecks,
            'delivery_fee_kopecks' => $deliveryFeeKopecks,
            'is_free' => $deliveryMethod === DeliveryMethod::Courier && $deliveryFeeKopecks === 0,
            'remaining_to_free_kopecks' => max(0, $remainingToFreeKopecks),
            'items_total_kopecks' => $currentKopecks,
            'grand_total_kopecks' => $grandTotalKopecks,
            'items_total_rub' => $currentKopecks / 100,
            'delivery_fee_rub' => $deliveryFeeKopecks / 100,
            'grand_total_rub' => $grandTotalKopecks / 100,
        ];
    }
}
