<?php

namespace Tests\Feature;

use App\Infrastructure\AggregatorIngress\Model\ING_PartnerSkuBinding;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class YandexFoodMenuTest extends TestCase
{
    #[Test]
    public function oauth_выдаёт_access_token(): void
    {
        config([
            'yandex_food.enabled' => true,
            'yandex_food.auth_token' => 'phpunit-yandex-bearer-token',
            'yandex_food.client_id' => 'phpunit-yandex-client-id',
            'yandex_food.client_secret' => 'phpunit-yandex-client-secret',
        ]);

        $response = $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => 'phpunit-yandex-client-id',
            'client_secret' => 'phpunit-yandex-client-secret',
        ]);

        $response->assertOk();
        $response->assertJsonPath('access_token', 'phpunit-yandex-bearer-token');
    }

    #[Test]
    public function composition_требует_bearer_и_отдаёт_partner_sku(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        config([
            'yandex_food.enabled' => true,
            'yandex_food.auth_token' => 'phpunit-yandex-bearer-token',
            'yandex_food.client_id' => 'phpunit-yandex-client-id',
            'yandex_food.client_secret' => 'phpunit-yandex-client-secret',
        ]);

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        ING_PartnerSkuBinding::query()->updateOrCreate(
            [
                'partner_code' => 'yandex-eda',
                'partner_sku' => 'YE-MENU-TEST-001',
            ],
            [
                'product_id' => $productId,
            ],
        );

        $unauthorized = $this->getJson('/api/yandex-food/menu/1/composition');
        $unauthorized->assertStatus(400);
        $unauthorized->assertJsonPath('reason', 'Access token has been expired. You should request a new one');

        $response = $this->getJson('/api/yandex-food/menu/1/composition', [
            'Authorization' => 'Bearer phpunit-yandex-bearer-token',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'categories',
            'items',
            'lastChange',
        ]);

        $items = $response->json('items', []);
        $matched = array_values(array_filter(
            $items,
            static fn (mixed $item): bool => is_array($item) && ($item['id'] ?? null) === 'YE-MENU-TEST-001',
        ));

        $this->assertNotEmpty($matched);
        $this->assertSame('YE-MENU-TEST-001', $matched[0]['id']);
    }

    #[Test]
    public function restaurants_отдаёт_place(): void
    {
        config([
            'yandex_food.enabled' => true,
            'yandex_food.auth_token' => 'phpunit-yandex-bearer-token',
        ]);

        $response = $this->getJson('/api/yandex-food/restaurants', [
            'Authorization' => 'Bearer phpunit-yandex-bearer-token',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'places' => [
                ['id', 'title', 'address'],
            ],
        ]);
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
