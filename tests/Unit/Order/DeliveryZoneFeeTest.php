<?php

namespace Tests\Unit\Order;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Order\Port\PromotionDeliveryPricingAdapter;
use PHPUnit\Framework\TestCase;

final class DeliveryZoneFeeTest extends TestCase
{
    public function test_fee_is_zone_price_below_threshold(): void
    {
        $adapter = $this->adapter();
        $zone = $this->remoteZone(false, 40_000);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->policy(100_000),
            deliveryMethod: 'courier',
            currentKopecks: 50_000,
            zone: $zone,
        );

        $this->assertSame(40_000, $fee);
    }

    public function test_fee_is_free_above_threshold_for_non_remote_zone(): void
    {
        $adapter = $this->adapter();
        $zone = $this->remoteZone(false, 40_000);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->policy(100_000),
            deliveryMethod: 'courier',
            currentKopecks: 150_000,
            zone: $zone,
        );

        $this->assertSame(0, $fee);
    }

    public function test_fee_stays_zone_price_above_threshold_for_remote_zone(): void
    {
        $adapter = $this->adapter();
        $zone = $this->remoteZone(true, 55_000);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->policy(100_000),
            deliveryMethod: 'courier',
            currentKopecks: 150_000,
            zone: $zone,
        );

        $this->assertSame(55_000, $fee);
    }

    public function test_fee_is_zero_without_matched_zone(): void
    {
        $adapter = $this->adapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->policy(100_000),
            deliveryMethod: 'courier',
            currentKopecks: 150_000,
            zone: null,
        );

        $this->assertSame(0, $fee);
    }

    private function adapter(): PromotionDeliveryPricingAdapter
    {
        return new PromotionDeliveryPricingAdapter(
            new class implements DeliveryConfigurationRepository
            {
                public function findPublic(): ?DeliveryConfiguration
                {
                    return null;
                }
            },
            new class implements PromotionPolicyRepository
            {
                public function find(): ?PromotionPolicy
                {
                    return null;
                }
            },
        );
    }

    private function remoteZone(bool $isRemote, int $fee): DeliveryZone
    {
        return new DeliveryZone(
            id: 'z1',
            name: 'Test',
            deliveryFeeKopecks: $fee,
            isRemote: $isRemote,
            geometry: [
                'type' => 'Polygon',
                'coordinates' => [[[0.0, 0.0], [1.0, 0.0], [1.0, 1.0], [0.0, 0.0]]],
            ],
        );
    }

    private function policy(int $thresholdKopecks): PromotionPolicy
    {
        return new PromotionPolicy(
            id: 1,
            giftRules: [],
            deliveryBenefit: [
                'is_active' => true,
                'free_delivery_threshold_kopecks' => $thresholdKopecks,
                'outside_zone_surcharge_kopecks' => 0,
                'below_threshold_fee_mode' => 'base',
                'in_zone_at_threshold_fee_mode' => 'free',
                'outside_zone_at_threshold_fee_mode' => 'outside_zone_surcharge_only',
            ],
            complementSetBenefit: [
                'rolls_per_set' => 2,
                'is_active' => false,
            ],
        );
    }
}
