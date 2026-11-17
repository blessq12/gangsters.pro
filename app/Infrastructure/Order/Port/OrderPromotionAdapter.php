<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Order\Port\OrderPromotionPort;
use App\Domain\Promotion\Port\PromotionDeliveryPricingPort;
use App\Domain\Promotion\Repository\ProductPromotionRoleRepository;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;

/**
 * Order → Promotion: правила подарка, роли товаров, комплемент-сеты и стоимость доставки по акции.
 */
final class OrderPromotionAdapter implements OrderPromotionPort
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly ProductPromotionRoleRepository $roles,
        private readonly PromotionDeliveryPricingPort $deliveryPricing,
    ) {}

    public function findGiftRuleForChannel(string $channel): ?array
    {
        return $this->policies->find()?->giftRuleForChannel($channel);
    }

    public function isGiftRuleAllowedOnDate(array $rule, \DateTimeInterface $at, string $timezone): bool
    {
        $policy = $this->policies->find();

        return $policy !== null && $policy->isGiftRuleAllowedOnDate($rule, $at, $timezone);
    }

    public function findRolesByProductIds(array $productIds): array
    {
        return $this->roles->findMetaByProductIds($productIds);
    }

    public function findActiveGiftCandidateProductIds(): array
    {
        return $this->roles->findActiveGiftCandidateProductIds();
    }

    public function findActiveComplementSetProductIds(): array
    {
        return $this->roles->findActiveComplementSetProductIds();
    }

    public function complementSetBenefit(): array
    {
        return $this->policies->find()?->complementSetBenefit()
            ?? ['rolls_per_set' => 0, 'max_sets' => 0, 'is_active' => false];
    }

    public function resolveDeliveryFee(
        ?string $deliveryMethod,
        int $currentKopecks,
        ?float $latitude,
        ?float $longitude,
    ): array {
        $zone = $this->deliveryPricing->resolveZone($latitude, $longitude);

        return [
            'fee_kopecks' => $this->deliveryPricing->resolveDeliveryFeeKopecks(
                $this->policies->find(),
                $deliveryMethod,
                $currentKopecks,
                $zone,
            ),
            'free_delivery_threshold_kopecks' => $this->deliveryPricing->resolveFreeDeliveryThresholdKopecks(),
            'zone_id' => $zone?->id(),
        ];
    }
}
