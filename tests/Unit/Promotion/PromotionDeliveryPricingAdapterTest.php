<?php

namespace Tests\Unit\Promotion;

use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\KitchenAddress;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\DeliveryFeeMode;
use App\Domain\Promotion\Enum\GiftBenefitType;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\ComplementSetBenefitRule;
use App\Domain\Promotion\ValueObject\DeliveryBenefitPolicy;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;
use App\Infrastructure\Promotion\Port\PromotionDeliveryPricingAdapter;
use App\Shared\Enum\DeliveryMethod;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PromotionDeliveryPricingAdapterTest extends TestCase
{
    private const THRESHOLD_KOPECKS = 100_000;

    private const BASE_FEE_KOPECKS = 40_000;

    private const OUTSIDE_ZONE_FEE_KOPECKS = 20_000;

    private const OUTSIDE_SURCHARGE_KOPECKS = 20_000;

    #[Test]
    public function без_политики_в_зоне_берёт_базовый_тариф(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS - 1,
            inZone: true,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function без_политики_вне_зоны_ниже_порога_берёт_базовый_тариф_и_доплату(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS - 1,
            inZone: false,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS + self::OUTSIDE_ZONE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function с_политикой_в_зоне_от_порога_доставка_бесплатная(): void
    {
        $adapter = $this->makeAdapter(withPromotionPolicy: true);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->promotionPolicy(),
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS,
            inZone: true,
        );

        $this->assertSame(0, $fee);
    }

    #[Test]
    public function с_политикой_вне_зоны_от_порога_только_надбавка(): void
    {
        $adapter = $this->makeAdapter(withPromotionPolicy: true);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->promotionPolicy(),
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS,
            inZone: false,
        );

        $this->assertSame(self::OUTSIDE_SURCHARGE_KOPECKS, $fee);
    }

    #[Test]
    public function с_политикой_в_зоне_ниже_порога_базовый_тариф(): void
    {
        $adapter = $this->makeAdapter(withPromotionPolicy: true);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->promotionPolicy(),
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS - 1,
            inZone: true,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function с_политикой_вне_зоны_ниже_порога_базовый_тариф_и_доплата(): void
    {
        $adapter = $this->makeAdapter(withPromotionPolicy: true);

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: $this->promotionPolicy(),
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::THRESHOLD_KOPECKS - 1,
            inZone: false,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS + self::OUTSIDE_ZONE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function порог_бесплатной_доставки_из_политики(): void
    {
        $adapter = $this->makeAdapter(withPromotionPolicy: true);

        $this->assertSame(self::THRESHOLD_KOPECKS, $adapter->resolveFreeDeliveryThresholdKopecks());
    }

    private function promotionPolicy(): PromotionPolicy
    {
        return new PromotionPolicy(
            id: 1,
            giftRules: [
                new GiftBenefitRule(
                    orderChannel: PromotionOrderChannel::Pickup,
                    minOrderAmountKopecks: self::THRESHOLD_KOPECKS,
                    benefitType: GiftBenefitType::FreeRollGift,
                    isActive: false,
                ),
            ],
            deliveryBenefitPolicy: new DeliveryBenefitPolicy(
                freeDeliveryThresholdKopecks: self::THRESHOLD_KOPECKS,
                outsideZoneSurchargeKopecks: self::OUTSIDE_SURCHARGE_KOPECKS,
                belowThresholdFeeMode: DeliveryFeeMode::BaseTariff,
                inZoneAtThresholdFeeMode: DeliveryFeeMode::Free,
                outsideZoneAtThresholdFeeMode: DeliveryFeeMode::OutsideZoneSurchargeOnly,
                isActive: true,
            ),
            complementSetBenefitRule: new ComplementSetBenefitRule(
                rollsPerSet: 2,
                isActive: false,
            ),
        );
    }

    private function makeAdapter(bool $withPromotionPolicy = false): PromotionDeliveryPricingAdapter
    {
        $deliveryRepository = $this->createMock(DeliveryConfigurationRepository::class);
        $deliveryRepository->method('findPublic')->willReturn(new DeliveryConfiguration(
            id: 1,
            minOrderAmountKopecks: self::THRESHOLD_KOPECKS,
            deliveryFeeKopecks: self::BASE_FEE_KOPECKS,
            outsideZoneDeliveryFeeKopecks: self::OUTSIDE_ZONE_FEE_KOPECKS,
            averageDeliveryTimeMinutes: 45,
            kitchenAddress: new KitchenAddress(
                city: 'Томск',
                street: 'пр. Ленина',
                house: '1',
                comment: null,
                searchLine: 'Томск, пр. Ленина, 1',
            ),
            kitchenLatitude: 56.48458,
            kitchenLongitude: 84.94817,
            deliveryZoneGeoJson: null,
        ));

        $promotionRepository = $this->createMock(PromotionPolicyRepository::class);
        if ($withPromotionPolicy) {
            $promotionRepository->method('find')->willReturn($this->promotionPolicy());
        } else {
            $promotionRepository->method('find')->willReturn(null);
        }

        return new PromotionDeliveryPricingAdapter($deliveryRepository, $promotionRepository);
    }
}
