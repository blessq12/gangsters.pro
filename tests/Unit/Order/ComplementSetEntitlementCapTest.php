<?php

namespace Tests\Unit\Order;

use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Order\Query\QuoteOrderUseCase;
use App\Domain\Content\Entity\DeliveryConfiguration;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\ValueObject\KitchenAddress;
use App\Domain\Order\Entity\PromotionPolicy;
use App\Domain\Order\Port\OrderCatalogPort;
use App\Domain\Order\Port\OrderClientLookupPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Infrastructure\Order\Port\PromotionDeliveryPricingAdapter;
use App\Shared\Geo\AddressGeocoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ComplementSetEntitlementCapTest extends TestCase
{
    #[DataProvider('rollCountEntitlementProvider')]
    public function test_entitled_sets_capped_by_max_sets(
        int $rollQty,
        int $expectedEntitled,
        int $expectedRemaining,
    ): void {
        $quote = $this->useCase()->execute($this->dto(
            lines: [['product_id' => 10, 'quantity' => $rollQty]],
        ));

        $this->assertSame($expectedEntitled, (int) $quote['benefits']['complement_entitled']);
        $this->assertSame(3, (int) $quote['benefits']['complement_max_sets']);
        $this->assertSame($rollQty, (int) $quote['benefits']['roll_count']);
        $this->assertSame($expectedRemaining, (int) $quote['benefits']['remaining_roll_count']);
    }

    /**
     * @return list<array{0: int, 1: int, 2: int}>
     */
    public static function rollCountEntitlementProvider(): array
    {
        return [
            '1 ролл → 1 комплект' => [1, 1, 2],
            '2 ролла → 1 комплект' => [2, 1, 1],
            '4 ролла → 2 (кап не срабатывает)' => [4, 2, 1],
            '6 роллов → 3 (потолок)' => [6, 3, 0],
            '10 роллов → 3 (не 5)' => [10, 3, 0],
        ];
    }

    public function test_complement_line_qty_defaults_to_capped_entitled(): void
    {
        $quote = $this->useCase()->execute($this->dto(
            lines: [['product_id' => 10, 'quantity' => 10]],
        ));

        $complementLines = $this->complementLines($quote);

        $this->assertCount(1, $complementLines);
        $this->assertSame(20, (int) $complementLines[0]['product_id']);
        $this->assertSame(3, (int) $complementLines[0]['quantity']);
    }

    public function test_complement_selection_clamped_to_entitled(): void
    {
        $quote = $this->useCase()->execute($this->dto(
            lines: [['product_id' => 10, 'quantity' => 10]],
            complementSelections: [20 => 9],
        ));

        $complementLines = $this->complementLines($quote);

        $this->assertCount(1, $complementLines);
        $this->assertSame(3, (int) $complementLines[0]['quantity']);
    }

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @param  array<int, int>  $complementSelections
     */
    private function dto(array $lines, array $complementSelections = []): QuoteOrderDto
    {
        return new QuoteOrderDto(
            lines: $lines,
            deliveryMethod: 'pickup',
            client: ['kind' => 'guest', 'name' => 'Test', 'phone' => '+7 (900) 111-22-33'],
            complementSelections: $complementSelections,
        );
    }

    /**
     * @param  array<string, mixed>  $quote
     * @return list<array<string, mixed>>
     */
    private function complementLines(array $quote): array
    {
        return array_values(array_filter(
            $quote['cart']['lines'],
            static fn (array $line): bool => ($line['payload']['kind'] ?? null) === 'complement',
        ));
    }

    private function useCase(): QuoteOrderUseCase
    {
        $products = [
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
            20 => [
                'id' => 20,
                'name' => 'Комплект',
                'sku' => 'COMP-1',
                'price_rubles' => 0,
                'is_active' => true,
                'is_system' => true,
                'ingredients' => [],
                'image_paths' => [],
            ],
        ];

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
                return isset($this->products[20]) ? [$this->products[20]] : [];
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
                        'counts_as_roll' => (int) $id === 10,
                        'complement_set' => (int) $id === 20,
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
                        'max_sets' => 3,
                        'is_active' => true,
                    ],
                );
            }
        };

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
            zones: [],
        );

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
