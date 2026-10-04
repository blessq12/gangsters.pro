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
use PHPUnit\Framework\TestCase;

final class DeliveryFeeLineQuoteTest extends TestCase
{
    public function test_quote_appends_delivery_line_when_zone_has_product(): void
    {
        $quote = $this->useCase(
            zoneProductId: 77,
            products: [
                10 => [
                    'id' => 10,
                    'name' => 'Ролл',
                    'sku' => 'ROLL-1',
                    'price_rubles' => 500,
                    'is_active' => true,
                    'is_system' => false,
                    'ingredients' => [],
                    'image_paths' => [],
                ],
                77 => [
                    'id' => 77,
                    'name' => 'Доставка дальняя',
                    'sku' => 'DELIVERY-REMOTE',
                    'price_rubles' => 250,
                    'is_active' => true,
                    'is_system' => true,
                    'ingredients' => [],
                    'image_paths' => [],
                ],
            ],
        )->execute(new QuoteOrderDto(
            lines: [['product_id' => 10, 'quantity' => 1]],
            deliveryMethod: 'courier',
            client: ['kind' => 'guest', 'name' => 'Test', 'phone' => '+7 (900) 111-22-33'],
            address: ['street' => 'ул. Тестовая', 'house' => '1'],
            latitude: 56.5,
            longitude: 85.0,
        ));

        $deliveryLines = array_values(array_filter(
            $quote['cart']['lines'],
            static fn (array $line): bool => ($line['payload']['kind'] ?? null) === 'delivery',
        ));

        $this->assertCount(1, $deliveryLines);
        $this->assertSame(77, (int) $deliveryLines[0]['product_id']);
        $this->assertSame('DELIVERY-REMOTE', $deliveryLines[0]['sku']);
        $this->assertSame(500, (int) $quote['totals']['items_rubles']);
        $this->assertSame(250, (int) $quote['totals']['delivery_fee_rubles']);
        $this->assertSame(750, (int) $quote['totals']['grand_total_rubles']);
    }

    public function test_quote_skips_delivery_line_without_product(): void
    {
        $quote = $this->useCase(
            zoneProductId: null,
            products: [
                10 => [
                    'id' => 10,
                    'name' => 'Ролл',
                    'sku' => 'ROLL-1',
                    'price_rubles' => 500,
                    'is_active' => true,
                    'is_system' => false,
                    'ingredients' => [],
                    'image_paths' => [],
                ],
            ],
        )->execute(new QuoteOrderDto(
            lines: [['product_id' => 10, 'quantity' => 1]],
            deliveryMethod: 'courier',
            client: ['kind' => 'guest', 'name' => 'Test', 'phone' => '+7 (900) 111-22-33'],
            address: ['street' => 'ул. Тестовая', 'house' => '1'],
            latitude: 56.5,
            longitude: 85.0,
        ));

        $deliveryLines = array_values(array_filter(
            $quote['cart']['lines'],
            static fn (array $line): bool => ($line['payload']['kind'] ?? null) === 'delivery',
        ));

        $this->assertCount(0, $deliveryLines);
        $this->assertSame(250, (int) $quote['totals']['delivery_fee_rubles']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     */
    private function useCase(?int $zoneProductId, array $products): QuoteOrderUseCase
    {
        $zone = new DeliveryZone(
            id: 'z-test',
            name: 'Тест',
            deliveryFeeKopecks: 25_000,
            isRemote: true,
            geometry: [
                'type' => 'Polygon',
                'coordinates' => [[[84.0, 56.0], [86.0, 56.0], [86.0, 57.0], [84.0, 57.0], [84.0, 56.0]]],
            ],
            deliveryProductId: $zoneProductId,
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
                    giftRules: [],
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
                return null;
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
