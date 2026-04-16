<?php

namespace Tests\Feature\Api;

final class ReleaseGoNoGoSmokeTest extends ApiTestCase
{
    public function test_release_public_api_baseline(): void
    {
        $this->getJson('/api/catalog')->assertOk();
        $this->getJson('/api/system/company')->assertOk();
        $this->getJson('/api/system/promotions')->assertOk();
        $this->getJson('/api/system/banners')->assertOk();
    }

    public function test_release_user_management_baseline(): void
    {
        $this->skipUnlessTablesExist([
            'UR_clients',
            'personal_access_tokens',
            'reporting_client_profiles',
        ]);

        $session = $this->registerClientViaApi();

        $this->postJson('/api/client/login', [
            'phone' => $session['phone'],
            'password' => $session['password'],
        ])->assertOk();

        $this->getJson('/api/client/profile', $this->bearerSanctum($session['token']))
            ->assertOk()
            ->assertJsonStructure(['client']);
    }

    public function test_release_place_order_baseline(): void
    {
        $this->skipUnlessTablesExist([
            'UR_clients',
            'personal_access_tokens',
            'ORD_orders',
            'ORD_order_items',
            'PRD_products',
            'PRD_category_product',
            'PRD_categories',
            'reporting_client_order_facts',
        ]);

        $session = $this->registerClientViaApi();
        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет активных товаров в каталоге для release-smoke заказа.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'customer_name' => 'Release Guest',
            'customer_phone' => '+79991230000',
        ])->assertCreated();

        $this->postJson(
            '/api/order',
            [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'delivery_method' => 'pickup',
                'payment_method' => 'card',
            ],
            $this->bearerSanctum($session['token']),
        )->assertCreated();
    }

    public function test_release_yandex_food_auth_baseline(): void
    {
        if (! (bool) config('services.yandex_food.enabled', true)) {
            $this->markTestSkipped('Yandex Food integration is disabled by config.');
        }

        $clientId = (string) config('services.yandex_food.client_id', '');
        $clientSecret = (string) config('services.yandex_food.client_secret', '');

        if ($clientId === '' || $clientSecret === '') {
            $this->markTestSkipped('YANDEX credentials отсутствуют: baseline проверяет только валидно сконфигурированное окружение.');
        }

        $this->postJson('/api/yandex-food/security/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ])->assertOk()->assertJsonStructure(['access_token']);
    }

    public function test_release_spa_shell_baseline(): void
    {
        $this->get('/')->assertOk();
        $this->get('/delivery')->assertOk();
        $this->get('/contacts')->assertOk();

        $appShellPath = resource_path('js/App.vue');
        $bootstrapPath = resource_path('js/processes/bootstrap/useAppBootstrap.js');
        $shoppingSessionProcessPath = resource_path('js/processes/shoppingSession/useShoppingSessionProcess.js');

        $this->assertFileExists($appShellPath);
        $this->assertFileExists($bootstrapPath);
        $this->assertFileExists($shoppingSessionProcessPath);

        $appCode = (string) file_get_contents($appShellPath);
        $bootstrapCode = (string) file_get_contents($bootstrapPath);

        $this->assertStringContainsString('useAppBootstrap', $appCode);
        $this->assertStringContainsString('useShoppingSessionProcess', $bootstrapCode);
    }
}
