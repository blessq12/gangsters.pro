<?php

namespace Tests\Feature;

use App\Infrastructure\Client\Model\CLN_Client;
use App\Infrastructure\Order\Model\ORD_Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ResolveRepeatableOrderLinesTest extends TestCase
{
    #[Test]
    public function repeatable_lines_возвращает_доступные_позиции_заказа(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $productId = $this->resolveFirstProductId();
        if ($productId === null) {
            $this->markTestSkipped('В каталоге нет активных товаров.');
        }

        $client = CLN_Client::query()->create([
            'name' => 'Повтор Тест',
            'phone' => '+7999'.random_int(1000000, 9999999),
            'email' => 'repeat-'.Str::uuid().'@example.test',
            'password' => Hash::make('secret'),
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $token = $client->createToken('test')->plainTextToken;

        $order = ORD_Order::query()->create([
            'source' => 'site',
            'checkout_id' => (string) Str::uuid(),
            'status' => 'new',
            'client_id' => $client->id,
            'total_rubles' => 450,
            'cart_snapshot' => [
                'lines' => [
                    [
                        'product_id' => $productId,
                        'product_name' => 'Тестовый товар',
                        'quantity' => 2,
                        'unit_price_rubles' => 450,
                        'line_total_rubles' => 900,
                        'payload' => null,
                    ],
                    [
                        'product_id' => 999999,
                        'product_name' => 'Снят с меню',
                        'quantity' => 1,
                        'unit_price_rubles' => 100,
                        'line_total_rubles' => 100,
                        'payload' => ['kind' => 'gift'],
                    ],
                ],
            ],
            'client_snapshot' => [
                'kind' => 'registered',
                'client_id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
            ],
            'delivery_snapshot' => [
                'method' => 'pickup',
                'address' => null,
            ],
            'payment_snapshot' => [
                'method' => 'cash',
            ],
            'created_at' => now(),
        ]);

        $response = $this->withToken($token)->getJson(
            '/api/order/'.$order->id.'/repeatable-lines',
        );

        $response->assertOk();
        $response->assertJsonPath('order_id', $order->id);
        $response->assertJsonCount(1, 'available_lines');
        $response->assertJsonPath('available_lines.0.product_id', $productId);
        $response->assertJsonPath('available_lines.0.quantity', 2);
        $response->assertJsonStructure([
            'available_lines' => [
                ['product_id', 'quantity', 'product_name', 'unit_price_rubles', 'catalog_kind'],
            ],
            'unavailable_lines',
        ]);
    }

    #[Test]
    public function repeatable_lines_запрещён_для_чужого_заказа(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $owner = CLN_Client::query()->create([
            'name' => 'Владелец',
            'phone' => '+7999'.random_int(1000000, 9999999),
            'email' => 'owner-'.Str::uuid().'@example.test',
            'password' => Hash::make('secret'),
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $intruder = CLN_Client::query()->create([
            'name' => 'Чужой',
            'phone' => '+7998'.random_int(1000000, 9999999),
            'email' => 'intruder-'.Str::uuid().'@example.test',
            'password' => Hash::make('secret'),
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $order = ORD_Order::query()->create([
            'source' => 'site',
            'checkout_id' => (string) Str::uuid(),
            'status' => 'new',
            'client_id' => $owner->id,
            'total_rubles' => 100,
            'cart_snapshot' => [
                'lines' => [
                    [
                        'product_id' => 1,
                        'product_name' => 'Товар',
                        'quantity' => 1,
                        'unit_price_rubles' => 100,
                        'line_total_rubles' => 100,
                        'payload' => null,
                    ],
                ],
            ],
            'client_snapshot' => [
                'kind' => 'registered',
                'client_id' => $owner->id,
                'name' => $owner->name,
                'phone' => $owner->phone,
            ],
            'delivery_snapshot' => [
                'method' => 'pickup',
                'address' => null,
            ],
            'payment_snapshot' => [
                'method' => 'cash',
            ],
            'created_at' => now(),
        ]);

        $token = $intruder->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson(
            '/api/order/'.$order->id.'/repeatable-lines',
        );

        $response->assertUnauthorized();
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
