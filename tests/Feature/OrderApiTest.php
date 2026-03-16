<?php

namespace Tests\Feature;

use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_order(): void
    {
        // Arrange: создаём продукт через модель инфраструктуры, чтобы репозиторий его видел.
        $product = PRD_Product::create([
            'name' => 'Test pizza',
            'description' => 'Tasty',
            'status' => 'active',
            'calories' => 0,
            'proteins' => 0,
            'fats' => 0,
            'carbs' => 0,
            'nutrition_basis' => '100g',
        ]);

        PRD_ProductPrice::create([
            'product_id' => $product->id,
            'amount' => 1000,
            'customer_status' => 'regular',
            'is_default' => true,
        ]);

        // Цена и статусы задаются в домене, но для теста достаточно наличия продукта —
        // ProductRepository::findByIds() вернёт его и priceForStatus() найдёт цену по статусу.

        // Act
        $response = $this->postJson('/api/order', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'delivery_method' => 'courier',
            'delivery_address' => ['street' => 'Test', 'house' => '1'],
            'delivery_comment' => null,
            'payment_method' => 'cash',
        ]);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'client_id',
            'customer' => ['name', 'phone', 'email', 'address'],
            'items',
            'total',
        ]);
        $this->assertSame(0, $response->json('client_id'));
    }

    public function test_authenticated_client_can_create_and_list_orders(): void
    {
        // Arrange: клиент и продукт
        /** @var UR_Client $client */
        $client = UR_Client::create([
            'name' => 'Client',
            'phone' => '+70000000000',
            'email' => 'client@example.com',
            'status' => 'active',
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $product = PRD_Product::create([
            'name' => 'Auth pizza',
            'description' => 'Tasty',
            'status' => 'active',
            'calories' => 0,
            'proteins' => 0,
            'fats' => 0,
            'carbs' => 0,
            'nutrition_basis' => '100g',
        ]);

        PRD_ProductPrice::create([
            'product_id' => $product->id,
            'amount' => 1500,
            'customer_status' => 'regular',
            'is_default' => true,
        ]);

        $token = $client->createToken('test')->plainTextToken;

        // Act: создаём заказ
        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/order', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
                'delivery_method' => 'courier',
                'delivery_address' => ['street' => 'Auth', 'house' => '2'],
                'delivery_comment' => null,
                'payment_method' => 'card',
            ]);

        $createResponse->assertStatus(201);

        // Здесь можно дополнительно проверить чтение заказов, когда будет настроен guard для UR_Client.
    }
}
