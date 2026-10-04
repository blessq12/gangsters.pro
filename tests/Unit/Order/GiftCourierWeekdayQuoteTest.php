<?php

namespace Tests\Unit\Order;

use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Content\ValueObject\KitchenAddress;
use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Port\OrderCatalogPort;
use App\Domain\Order\Port\OrderClientLookupPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Order\Port\PromotionDeliveryPricingAdapter;
use App\Shared\Geo\AddressGeocoder;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

final class GiftCourierWeekdayQuoteTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_courier_gift_eligible_on_monday_above_threshold(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-05 15:00:00', 'Asia/Tomsk')); // пн

        $quote = $this->useCase()->execute($this->courierDto(amountRubles: 1900));

        $this->assertTrue((bool) $quote['benefits']['gift_active']);
        $this->assertTrue((bool) $quote['benefits']['gift_weekday_ok']);
        $this->assertTrue((bool) $quote['benefits']['gift_eligible']);
        $this->assertSame([1, 2, 3, 4], $quote['benefits']['gift_allowed_weekdays']);
    }

    public function test_courier_gift_blocked_on_friday_above_threshold(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00', 'Asia/Tomsk')); // пт

        $quote = $this->useCase()->execute($this->courierDto(amountRubles: 1900));

        $this->assertTrue((bool) $quote['benefits']['gift_active']);
        $this->assertFalse((bool) $quote['benefits']['gift_weekday_ok']);
        $this->assertFalse((bool) $quote['benefits']['gift_eligible']);
    }

    public function test_courier_gift_uses_scheduled_at_weekday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00', 'Asia/Tomsk')); // пт сейчас

        $quote = $this->useCase()->execute($this->courierDto(
            amountRubles: 1900,
            scheduledAt: '2026-10-05 19:00:00', // пн
        ));

        $this->assertTrue((bool) $quote['benefits']['gift_weekday_ok']);
        $this->assertTrue((bool) $quote['benefits']['gift_eligible']);
    }

    public function test_pickup_gift_ignores_courier_weekdays(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00', 'Asia/Tomsk')); // пт

        $quote = $this->useCase()->execute(new QuoteOrderDto(
            lines: [['product_id' => 10, 'quantity' => 1]],
            deliveryMethod: 'pickup',
            client: ['kind' => 'guest', 'phone' => '+70000000000'],
        ));

        $this->assertTrue((bool) $quote['benefits']['gift_weekday_ok']);
        $this->assertTrue((bool) $quote['benefits']['gift_eligible']);
    }

    private function courierDto(int $amountRubles, ?string $scheduledAt = null): QuoteOrderDto
    {
        return new QuoteOrderDto(
            lines: [['product_id' => 10, 'quantity' => 1]],
            deliveryMethod: 'courier',
            client: ['kind' => 'guest', 'phone' => '+70000000000'],
            address: [
                'street' => 'Ленина',
                'house' => '1',
                'city' => 'Томск',
            ],
            scheduledAt: $scheduledAt,
            latitude: 56.5,
            longitude: 85.0,
        );
    }

    private function useCase(): QuoteOrderUseCase
    {
        $products = [
            10 => [
                'id' => 10,
                'name' => 'Ролл',
                'price_rubles' => 1900,
                'is_system' => false,
                'image_path' => null,
                'image_paths' => [],
                'ingredients' => [],
            ],
            99 => [
                'id' => 99,
                'name' => 'Подарок',
                'price_rubles' => 0,
                'is_system' => true,
                'image_path' => null,
                'image_paths' => [],
                'ingredients' => [],
            ],
        ];

        $zone = new DeliveryZone(
            id: 'z-test',
            name: 'Тест',
            deliveryFeeKopecks: 25_000,
            isRemote: false,
            geometry: [
                'type' => 'Polygon',
                'coordinates' => [[[84.0, 56.0], [86.0, 56.0], [86.0, 57.0], [84.0, 57.0], [84.0, 56.0]]],
            ],
            deliveryProductId: null,
        );

        $deliveryConfig = new DeliveryConfiguration(
            id: 1,
            minOrderAmountKopecks: null,
            averageDeliveryTimeMinutes: null,
            kitchenAddress: new KitchenAddress(
                city: 'Томск',
                street: null,
                house: null,
                comment: null,
                searchLine: null,
            ),
            kitchenLatitude: null,
            kitchenLongitude: null,
            zones: [$zone],
        );

        $catalog = new class($products) implements OrderCatalogPort
        {
            /** @param  array<int, array<string, mixed>>  $products */
            public function __construct(private array $products) {}

            public function findActiveProductsByIds(array $ids): array
            {
                $out = [];
                foreach ($ids as $id) {
                    if (isset($this->products[$id]) && ! ($this->products[$id]['is_system'] ?? false)) {
                        $out[] = $this->products[$id];
                    }
                }

                return $out;
            }

            public function findActiveSetsByIds(array $ids): array
            {
                return [];
            }

            public function findActiveSystemProducts(): array
            {
                return array_values(array_filter(
                    $this->products,
                    static fn (array $p): bool => (bool) ($p['is_system'] ?? false),
                ));
            }

            public function findActiveComplementSetProducts(): array
            {
                return [];
            }

            public function findProductById(int $id): ?array
            {
                return $this->products[$id] ?? null;
            }

            public function findPromotionMetaByProductIds(array $ids): array
            {
                $meta = [];
                foreach ($ids as $id) {
                    $meta[(int) $id] = [
                        'counts_as_roll' => true,
                        'complement_set' => false,
                    ];
                }

                return $meta;
            }
        };

        $policyRepo = new class implements PromotionPolicyRepository
        {
            public function find(): ?PromotionPolicy
            {
                return new PromotionPolicy(
                    id: 1,
                    giftRules: [
                        [
                            'order_channel' => 'pickup',
                            'min_order_amount_kopecks' => 100_000,
                            'benefit_type' => 'free_roll_gift',
                            'is_active' => true,
                            'allowed_weekdays' => [],
                        ],
                        [
                            'order_channel' => 'courier',
                            'min_order_amount_kopecks' => 180_000,
                            'benefit_type' => 'free_roll_gift',
                            'is_active' => true,
                            'allowed_weekdays' => [1, 2, 3, 4],
                        ],
                    ],
                    deliveryBenefit: [
                        'is_active' => false,
                        'free_delivery_threshold_kopecks' => 0,
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
        };

        $deliveryRepo = new class($deliveryConfig) implements DeliveryConfigurationRepository
        {
            public function __construct(private DeliveryConfiguration $config) {}

            public function findPublic(): ?DeliveryConfiguration
            {
                return $this->config;
            }
        };

        $geocoder = new class implements AddressGeocoder
        {
            public function geocode(string $street, string $house, ?string $city = null): ?array
            {
                return ['latitude' => 56.5, 'longitude' => 85.0];
            }

            public function geocodeQuery(string $query): ?array
            {
                return null;
            }
        };

        $clientLookup = new class implements OrderClientLookupPort
        {
            public function findSnapshotById(int $clientId): ?array
            {
                return null;
            }
        };

        return new QuoteOrderUseCase(
            catalog: $catalog,
            promotionPolicies: $policyRepo,
            deliveryPricing: new PromotionDeliveryPricingAdapter($deliveryRepo, $policyRepo),
            addressGeocoder: $geocoder,
            clientLookup: $clientLookup,
        );
    }
}
