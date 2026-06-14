<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CheckoutApiTest extends TestCase
{
    #[Test]
    public function post_checkout_и_get_восстанавливают_черновик(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $createResponse->assertJsonStructure([
            'checkout_id',
            'status',
            'cart',
            'wizard' => [
                'suggested_step',
                'can_confirm',
                'missing_blocks',
            ],
            'order_preview' => [
                'complement_lines',
                'auto_lines',
                'gift_summary',
                'gift_cta',
                'totals',
                'benefits',
            ],
        ]);

        $checkoutId = $createResponse->json('checkout_id');
        $this->assertNotEmpty($checkoutId);

        $getResponse = $this->getJson('/api/checkout/'.$checkoutId);
        $getResponse->assertOk();
        $getResponse->assertJsonPath('checkout_id', $checkoutId);
        $getResponse->assertJsonPath('status', 'draft');
    }

    #[Test]
    public function get_checkout_для_неизвестного_id_возвращает_404(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/checkout/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    #[Test]
    public function patch_cart_возвращает_benefits_progress(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $checkoutId = (string) $createResponse->json('checkout_id');

        $patchResponse = $this->patchJson('/api/checkout/'.$checkoutId.'/cart', [
            'product_id' => $productId,
            'quantity' => 1,
        ]);

        $patchResponse->assertOk();
        $patchResponse->assertJsonStructure([
            'benefits_progress' => [
                'delivery',
                'gift',
                'complement',
            ],
        ]);
    }

    #[Test]
    public function patch_delivery_с_адресом_в_зоне_возвращает_in_zone_true(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        if (! $this->deliveryZoneIsConfigured()) {
            $this->markTestSkipped('Зона доставки не настроена в БД.');
        }

        $insidePoint = $this->resolvePointInsideDeliveryZone();
        if ($insidePoint === null) {
            $this->markTestSkipped('Не удалось определить точку внутри зоны доставки.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $checkoutId = (string) $createResponse->json('checkout_id');

        $response = $this->patchJson('/api/checkout/'.$checkoutId.'/delivery', [
            'method' => 'courier',
            'address' => [
                'street' => 'пр. Ленина',
                'house' => '1',
                'latitude' => $insidePoint['latitude'],
                'longitude' => $insidePoint['longitude'],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('delivery_pricing.in_zone', true);
    }

    #[Test]
    public function patch_delivery_с_адресом_вне_зоны_применяет_доплату(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        if (! $this->deliveryZoneIsConfigured()) {
            $this->markTestSkipped('Зона доставки не настроена в БД.');
        }

        $deliveryResponse = $this->getJson('/api/delivery');
        $deliveryResponse->assertOk();
        $baseFeeKopecks = (int) $deliveryResponse->json('data.settings.delivery_fee_kopecks');
        $outsideFeeKopecks = (int) $deliveryResponse->json('data.settings.outside_zone_delivery_fee_kopecks');

        if ($outsideFeeKopecks <= $baseFeeKopecks) {
            $this->markTestSkipped('В конфигурации доставки не задана доплата за отдалённый район.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $checkoutId = (string) $createResponse->json('checkout_id');

        $response = $this->patchJson('/api/checkout/'.$checkoutId.'/delivery', [
            'method' => 'courier',
            'address' => [
                'street' => 'ул. Тестовая',
                'house' => '999',
                'latitude' => 55.0,
                'longitude' => 84.0,
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('delivery_pricing.in_zone', false);
        $response->assertJsonPath(
            'delivery_pricing.delivery_fee_kopecks',
            $baseFeeKopecks + $outsideFeeKopecks,
        );
        $response->assertJsonPath('delivery_pricing.base_delivery_fee_kopecks', $baseFeeKopecks);
        $response->assertJsonPath(
            'delivery_pricing.outside_zone_surcharge_kopecks',
            $outsideFeeKopecks,
        );
    }

    private function deliveryZoneIsConfigured(): bool
    {
        return $this->resolvePointInsideDeliveryZone() !== null;
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function resolvePointInsideDeliveryZone(): ?array
    {
        $deliveryResponse = $this->getJson('/api/delivery');
        if (! $deliveryResponse->isOk()) {
            return null;
        }

        $geoJson = $deliveryResponse->json('data.zone.delivery_zone_geojson');
        if (! is_array($geoJson) || ($geoJson['type'] ?? null) !== 'Polygon') {
            return null;
        }

        $ring = $geoJson['coordinates'][0] ?? null;
        if (! is_array($ring) || count($ring) < 3) {
            return null;
        }

        $longitudeSum = 0.0;
        $latitudeSum = 0.0;
        $count = 0;

        foreach ($ring as $vertex) {
            if (! is_array($vertex) || count($vertex) < 2) {
                continue;
            }

            $longitudeSum += (float) $vertex[0];
            $latitudeSum += (float) $vertex[1];
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return [
            'latitude' => $latitudeSum / $count,
            'longitude' => $longitudeSum / $count,
        ];
    }

    #[Test]
    public function patch_cart_добавляет_набор_в_корзину(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $setId = $this->resolveFirstSetId();
        if ($setId === null) {
            $this->markTestSkipped('В каталоге нет активных наборов.');
        }

        $createResponse = $this->postJson('/api/checkout');
        $createResponse->assertCreated();
        $checkoutId = (string) $createResponse->json('checkout_id');

        $patchResponse = $this->patchJson('/api/checkout/'.$checkoutId.'/cart', [
            'product_id' => $setId,
            'quantity' => 1,
            'payload' => ['catalog_kind' => 'set'],
        ]);

        $patchResponse->assertOk();
        $patchResponse->assertJsonPath('cart.items.0.product_id', $setId);
        $patchResponse->assertJsonPath('cart.items.0.quantity', 1);
    }

    private function resolveFirstSetId(): ?int
    {
        $catalogResponse = $this->getJson('/api/catalog');
        $catalogResponse->assertOk();

        foreach ($catalogResponse->json('categories', []) as $categoryNode) {
            foreach ($categoryNode['items'] ?? [] as $item) {
                if (($item['kind'] ?? null) === 'set' && isset($item['id'])) {
                    return (int) $item['id'];
                }
            }
        }

        return null;
    }

    private function resolveFirstProductId(): ?int
    {
        $catalogResponse = $this->getJson('/api/catalog');
        $catalogResponse->assertOk();

        foreach ($catalogResponse->json('categories', []) as $categoryNode) {
            foreach ($categoryNode['items'] ?? [] as $item) {
                if (($item['kind'] ?? null) === 'product' && isset($item['id'])) {
                    return (int) $item['id'];
                }
            }
        }

        return null;
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
