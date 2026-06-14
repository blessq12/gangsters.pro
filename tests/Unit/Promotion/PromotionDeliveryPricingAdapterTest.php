<?php

namespace Tests\Unit\Promotion;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Delivery\ValueObject\KitchenAddress;
use App\Infrastructure\Promotion\Port\PromotionDeliveryPricingAdapter;
use App\Shared\Enum\DeliveryMethod;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PromotionDeliveryPricingAdapterTest extends TestCase
{
    private const MIN_ORDER_KOPECKS = 150_000;

    private const BASE_FEE_KOPECKS = 20_000;

    private const OUTSIDE_FEE_KOPECKS = 50_000;

    #[Test]
    public function в_зоне_ниже_минимальной_суммы_берёт_базовый_тариф(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::MIN_ORDER_KOPECKS - 1,
            inZone: true,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function в_зоне_от_минимальной_суммы_доставка_бесплатная(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::MIN_ORDER_KOPECKS,
            inZone: true,
        );

        $this->assertSame(0, $fee);
    }

    #[Test]
    public function вне_зоны_от_минимальной_суммы_берёт_тариф_за_пределами_зоны(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::MIN_ORDER_KOPECKS,
            inZone: false,
        );

        $this->assertSame(self::OUTSIDE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function вне_зоны_ниже_минимальной_суммы_берёт_базовый_тариф_и_доплату(): void
    {
        $adapter = $this->makeAdapter();

        $fee = $adapter->resolveDeliveryFeeKopecks(
            promotionPolicy: null,
            deliveryMethod: DeliveryMethod::Courier,
            currentKopecks: self::MIN_ORDER_KOPECKS - 1,
            inZone: false,
        );

        $this->assertSame(self::BASE_FEE_KOPECKS + self::OUTSIDE_FEE_KOPECKS, $fee);
    }

    #[Test]
    public function порог_бесплатной_доставки_совпадает_с_минимальной_суммой_заказа(): void
    {
        $adapter = $this->makeAdapter();

        $this->assertSame(self::MIN_ORDER_KOPECKS, $adapter->resolveFreeDeliveryThresholdKopecks());
    }

    private function makeAdapter(): PromotionDeliveryPricingAdapter
    {
        $repository = $this->createMock(DeliveryConfigurationRepository::class);
        $repository->method('findPublic')->willReturn(new DeliveryConfiguration(
            id: 1,
            minOrderAmountKopecks: self::MIN_ORDER_KOPECKS,
            deliveryFeeKopecks: self::BASE_FEE_KOPECKS,
            outsideZoneDeliveryFeeKopecks: self::OUTSIDE_FEE_KOPECKS,
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

        return new PromotionDeliveryPricingAdapter($repository);
    }
}
