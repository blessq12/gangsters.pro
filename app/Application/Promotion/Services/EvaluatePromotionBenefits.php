<?php

namespace App\Application\Promotion\Services;

use App\Application\Promotion\DTO\PromotionBenefitsInput;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Shared\Enum\DeliveryMethod;

/**
 * Оркестрация расчёта benefits_progress, delivery_pricing и promo_state.
 */
final class EvaluatePromotionBenefits
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly EvaluateGiftBenefits $giftBenefits,
        private readonly EvaluateDeliveryBenefits $deliveryBenefits,
        private readonly EvaluateComplementBenefits $complementBenefits,
    ) {}

    /**
     * @return array{
     *     benefits_progress: array<string, mixed>,
     *     delivery_pricing: array<string, mixed>|null,
     *     promo_state: array<string, mixed>
     * }
     */
    public function evaluate(PromotionBenefitsInput $input): array
    {
        $promotionPolicy = $this->promotionPolicies->find();

        $giftBenefit = $this->giftBenefits->buildBenefit(
            promotionPolicy: $promotionPolicy,
            orderChannel: $input->orderChannel,
            currentKopecks: $input->currentKopecks,
        );

        $deliveryBenefit = $this->deliveryBenefits->buildBenefit(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $input->deliveryMethod,
            currentKopecks: $input->currentKopecks,
        );

        $deliveryFeeKopecks = $this->deliveryBenefits->resolveDeliveryFeeKopecks(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $input->deliveryMethod,
            currentKopecks: $input->currentKopecks,
            deliveryLatitude: $input->deliveryLatitude,
            deliveryLongitude: $input->deliveryLongitude,
        );

        $previewDeliveryFeeKopecks = $this->deliveryBenefits->resolveDeliveryFeeKopecks(
            promotionPolicy: $promotionPolicy,
            deliveryMethod: $input->deliveryMethod ?? DeliveryMethod::Courier,
            currentKopecks: $input->currentKopecks,
            deliveryLatitude: $input->deliveryLatitude,
            deliveryLongitude: $input->deliveryLongitude,
        );

        $giftPromotion = $this->giftBenefits->buildPromotionState(
            giftRule: $promotionPolicy?->giftRuleForChannel($input->orderChannel),
            currentKopecks: $input->currentKopecks,
            selectedGiftProductId: $input->selectedGiftProductId,
            giftCandidates: $input->giftCandidates,
        );

        $complementBenefit = $this->complementBenefits->buildBenefit(
            promotionPolicy: $promotionPolicy,
            rollCount: $input->rollCount,
            complementCandidates: $input->complementCandidates,
        );

        $complementPromotion = $this->complementBenefits->buildPromotionState(
            complementRule: $promotionPolicy?->complementSetBenefitRule(),
            rollCount: $input->rollCount,
            complementCandidates: $input->complementCandidates,
        );

        $deliveryPricing = $this->deliveryBenefits->buildPricing(
            deliveryMethod: $input->deliveryMethod,
            currentKopecks: $input->currentKopecks,
            deliveryFeeKopecks: $input->deliveryMethod === null
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
}
