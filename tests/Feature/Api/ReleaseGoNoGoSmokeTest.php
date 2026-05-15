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
        $home = $this->get('/')->assertOk();
        $this->get('/delivery')->assertOk();
        $this->get('/contacts')->assertOk();

        $html = $home->getContent();
        $this->assertIsString($html);
        $this->assertStringContainsString('meta name="description"', $html);
        $this->assertStringContainsString('name="theme-color" content="#191919"', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('rel="manifest" href="/favicon/site.webmanifest"', $html);
        $this->assertStringContainsString('apple-mobile-web-app-capable" content="yes"', $html);
        $this->assertStringContainsString(
            'apple-mobile-web-app-title" content="'.config('site.apple_mobile_web_app_title').'"',
            $html,
        );
        $this->assertStringContainsString('Гангстерс', $html);
        $this->assertStringContainsString('href="/favicon/favicon.svg"', $html);
        $this->assertStringContainsString('href="/favicon/favicon.ico"', $html);

        $manifest = $this->get('/favicon/site.webmanifest')->assertOk();
        $manifest->assertHeader('content-type', 'application/manifest+json; charset=UTF-8');
        $manifestJson = $manifest->json();
        $this->assertIsArray($manifestJson);
        $this->assertSame((string) config('site.name'), $manifestJson['name'] ?? null);
        $this->assertSame((string) config('site.short_name'), $manifestJson['short_name'] ?? null);
        $this->assertStringContainsString('Гангстерс', (string) ($manifestJson['short_name'] ?? ''));
        $this->assertSame('/?utm_source=pwa', $manifestJson['start_url'] ?? null);
        $this->assertSame('standalone', $manifestJson['display'] ?? null);
        $this->assertNotEmpty($manifestJson['icons'] ?? null);
        $iconSizes = array_column($manifestJson['icons'], 'sizes');
        $this->assertContains('192x192', $iconSizes);
        $this->assertContains('512x512', $iconSizes);

        foreach ($manifestJson['icons'] as $icon) {
            $this->assertIsArray($icon);
            $this->assertNotEmpty($icon['src'] ?? null);
            $this->assertNotEmpty($icon['sizes'] ?? null);
            $this->assertNotEmpty($icon['type'] ?? null);
        }

        $faviconPaths = array_unique(array_merge(
            array_column((array) config('site.manifest_icons', []), 'path'),
            [
                (string) config('site.apple_touch_icon'),
                (string) config('site.favicon_ico'),
                (string) config('site.favicon_png_96'),
            ],
        ));

        foreach ($faviconPaths as $path) {
            $response = $this->get($path)->assertOk();
            if (str_ends_with($path, '.png')) {
                $this->assertStringContainsString(
                    'image/png',
                    (string) $response->headers->get('Content-Type'),
                );
            }
            if (str_ends_with($path, '.ico')) {
                $this->assertMatchesRegularExpression(
                    '#image/(x-icon|vnd\.microsoft\.icon)#',
                    (string) $response->headers->get('Content-Type'),
                );
            }
        }

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertHeader('content-type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString('<urlset', (string) $sitemap->getContent());

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
