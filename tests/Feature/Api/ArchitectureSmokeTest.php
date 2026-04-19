<?php

namespace Tests\Feature\Api;

final class ArchitectureSmokeTest extends ApiTestCase
{
    public function test_public_and_auth_api_smoke_routes(): void
    {
        $this->getJson('/api/catalog')->assertOk();
        $this->getJson('/api/system/company')->assertOk();
        $this->getJson('/api/client/profile')->assertUnauthorized();
        $this->getJson('/api/order')->assertUnauthorized();
    }

    public function test_client_and_order_auth_smoke_flow(): void
    {
        $this->skipUnlessTablesExist([
            'UR_clients',
            'personal_access_tokens',
            'ORD_orders',
            'ORD_order_items',
            'PRD_products',
            'PRD_category_product',
            'PRD_categories',
            'reporting_client_profiles',
            'reporting_client_order_facts',
            'SHP_shopping_sessions',
            'SHP_shopping_cart_lines',
        ]);

        $productId = $this->firstProductIdFromCatalog();
        if ($productId === null) {
            $this->markTestSkipped('Нет товаров в каталоге.');
        }

        $this->postJson('/api/order', [
            'items' => [['product_id' => $productId, 'quantity' => 1]],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'customer_name' => 'Smoke Guest',
            'customer_phone' => '+79991234567',
        ])->assertCreated();

        $session = $this->registerClientViaApi();

        $this->getJson('/api/client/profile', $this->bearerSanctum($session['token']))
            ->assertOk()
            ->assertJsonStructure(['client']);

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

    public function test_spa_shell_and_checkout_entrypoint_smoke(): void
    {
        $this->get('/')->assertOk();
        $this->get('/delivery')->assertOk();
        $this->get('/contacts')->assertOk();

        $checkoutDockPath = resource_path('js/components/dock/CartDockPanel.vue');
        $checkoutFlowPath = resource_path('js/composables/checkout/useCheckoutFlow.js');
        $checkoutContextPath = resource_path('js/composables/checkout/checkoutFlowContext.js');

        $this->assertFileExists($checkoutDockPath);
        $this->assertFileExists($checkoutFlowPath);
        $this->assertFileExists($checkoutContextPath);

        $dockCode = (string) file_get_contents($checkoutDockPath);
        $flowCode = (string) file_get_contents($checkoutFlowPath);
        $contextCode = (string) file_get_contents($checkoutContextPath);

        $this->assertStringContainsString('useCheckoutFlow', $dockCode);
        $this->assertStringContainsString('provideCheckoutFlow', $dockCode);
        $this->assertStringContainsString('useCheckoutOrchestrator', $flowCode);
        $this->assertStringContainsString('CHECKOUT_FLOW_KEY', $contextCode);
    }
}
