<?php

namespace Tests\Feature\Crm;

use Tests\ApiTestCase;

final class ClientApiTest extends ApiTestCase
{
    public function test_register_creates_client_and_token(): void
    {
        $phone = $this->uniquePhone();

        $response = $this->postJson('/api/client/register', [
            'name' => 'Anna Test',
            'phone' => $phone,
            'password' => 'secret12',
            'consent_personal_data' => true,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'client' => ['id', 'name', 'phone'],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame('Anna Test', $response->json('client.name'));
    }

    public function test_login_by_phone_returns_token(): void
    {
        $account = $this->registerClient();

        $response = $this->postJson('/api/client/login', [
            'phone' => $account['phone'],
            'password' => $account['password'],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'client' => ['id']])
            ->assertJsonPath('client.id', $account['client']['id']);
    }

    public function test_profile_can_be_read_and_updated(): void
    {
        $account = $this->registerClient();

        $read = $this->withBearer($account['token'])
            ->getJson('/api/client/profile');

        $read->assertOk()
            ->assertJsonPath('client.id', $account['client']['id']);

        $update = $this->withBearer($account['token'])
            ->patchJson('/api/client/profile', [
                'name' => 'New Name',
                'consent_marketing' => true,
            ]);

        $update->assertOk()
            ->assertJsonPath('client.name', 'New Name')
            ->assertJsonPath('client.consent_marketing', true);
    }

    public function test_address_can_be_added_and_deleted(): void
    {
        $account = $this->registerClient();

        $create = $this->withBearer($account['token'])
            ->postJson('/api/client/addresses', [
                'street' => 'Main Street',
                'house' => '10',
                'apartment' => '5',
                'make_default' => true,
            ]);

        $create->assertCreated();
        $addresses = $create->json('client.addresses');
        $this->assertCount(1, $addresses);
        $addressId = $addresses[0]['id'];
        $this->assertTrue($addresses[0]['is_default']);

        $delete = $this->withBearer($account['token'])
            ->deleteJson('/api/client/addresses/'.$addressId);

        $delete->assertOk();
        $this->assertSame([], $delete->json('client.addresses'));
    }

    public function test_favorites_toggle_remove_and_merge(): void
    {
        $account = $this->registerClient();
        $productId = $this->activeProductId();
        $otherProductId = $productId + 1;

        $toggle = $this->withBearer($account['token'])
            ->postJson('/api/client/favorites/'.$productId, [
                'name' => 'Test Roll',
                'price' => 500,
            ]);

        $toggle->assertOk();
        $this->assertCount(1, $toggle->json('favorites'));
        $this->assertSame($productId, (int) $toggle->json('favorites.0.productId'));

        $list = $this->withBearer($account['token'])
            ->getJson('/api/client/favorites');

        $list->assertOk()
            ->assertJsonCount(1, 'favorites');

        $merge = $this->withBearer($account['token'])
            ->postJson('/api/client/favorites/merge', [
                'items' => [
                    [
                        'product_id' => $otherProductId,
                        'product_name' => 'Guest Product',
                        'price_rub' => 100,
                    ],
                ],
            ]);

        $merge->assertOk();
        $this->assertGreaterThanOrEqual(2, count($merge->json('favorites')));

        $remove = $this->withBearer($account['token'])
            ->deleteJson('/api/client/favorites/'.$productId);

        $remove->assertOk();
        $remaining = collect($remove->json('favorites'))
            ->pluck('productId')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertNotContains($productId, $remaining);
    }

    public function test_order_history_and_repeatable_lines(): void
    {
        $account = $this->registerClient();
        $clientId = (int) $account['client']['id'];
        $productId = $this->activeProductId();

        $order = $this->placeOrderForClient(
            $account['token'],
            $clientId,
            $productId,
        );

        $history = $this->withBearer($account['token'])
            ->getJson('/api/client/orders');

        $history->assertOk()
            ->assertJsonStructure(['data']);

        $ids = collect($history->json('data'))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertContains($order['order_id'], $ids);

        $repeatable = $this->withBearer($account['token'])
            ->getJson('/api/client/orders/'.$order['order_id'].'/repeatable-lines');

        $repeatable->assertOk()
            ->assertJsonStructure([
                'available_lines',
                'unavailable_lines',
            ]);

        $availableIds = collect($repeatable->json('available_lines'))
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->assertContains($productId, $availableIds);
    }

    public function test_protected_endpoints_without_token_return_401(): void
    {
        $this->getJson('/api/client/profile')->assertUnauthorized();
        $this->getJson('/api/client/favorites')->assertUnauthorized();
        $this->getJson('/api/client/orders')->assertUnauthorized();
    }
}
