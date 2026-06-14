<?php

namespace App\Application\Checkout\Services;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Port\CatalogGiftCandidate;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
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
        $selectedGiftProductId = $this->resolveSelectedGiftProductId($checkout);

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
            ],
            'delivery_pricing' => $deliveryPricing,
            'promo_state' => [
                'gift_promotion' => $giftPromotion,
            ],
        ];
    }

    private function payableTotalKopecks(Checkout $checkout): int
    {
        $totalRubles = 0;

        foreach ($checkout->cart()->lines() as $line) {
            if ($this->isGiftLine($line)) {
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
        if ($deliveryMethod !== DeliveryMethod::Courier) {
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
