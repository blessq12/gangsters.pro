<?php

namespace Tests\Feature\Api;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Integrations\FrontpadOrderGateway;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(FrontpadOrderGateway::class, fn () => new class implements FrontpadOrderGateway {
            public function pushOrder(Order $order): void {}
        });
    }

    /**
     * Без мигрированной БД тесты осмысленно не гоняем (без RefreshDatabase по твоей политике).
     * На части MySQL имена фактически в нижнем регистре — проверяем оба варианта.
     *
     * @param  array<int, string>  $tables
     */
    protected function skipUnlessTablesExist(array $tables): void
    {
        foreach ($tables as $table) {
            if (! $this->databaseTableExists($table)) {
                $this->markTestSkipped(
                    "Нет таблицы `{$table}` — выполни миграции на выбранной для тестов БД.",
                );
            }
        }
    }

    protected function databaseTableExists(string $table): bool
    {
        if (Schema::hasTable($table)) {
            return true;
        }

        $lower = strtolower($table);

        return $lower !== $table && Schema::hasTable($lower);
    }

    /**
     * Номер в формате +79XXXXXXXXX (11 цифр после +7), чтобы {@see \App\Infrastructure\Client\Repository\ClientRepository::formatPhoneForStorage}
     * приводил его к одному каноническому виду и совпадали existsByPhone / save / login.
     */
    protected function uniquePhone(): string
    {
        return '+79'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
    }

    protected function uniqueEmail(): string
    {
        return 'api-test-'.uniqid('', true).'@example.test';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function registerPayload(string $phone, string $password = 'secret12', array $overrides = []): array
    {
        return array_merge([
            'name' => 'Api Test User',
            'phone' => $phone,
            'email' => null,
            'birth_date' => null,
            'password' => $password,
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ], $overrides);
    }

    /**
     * @return array{token: string, client: array<string, mixed>, phone: string, password: string}
     */
    protected function registerClientViaApi(string $password = 'secret12', array $overrides = []): array
    {
        $phone = $this->uniquePhone();
        $response = $this->postJson('/api/client/register', $this->registerPayload($phone, $password, $overrides));
        $response->assertOk();
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('client', $data);
        $this->assertIsString($data['token']);
        $this->assertIsArray($data['client']);

        return [
            'token' => $data['token'],
            'client' => $data['client'],
            'phone' => $phone,
            'password' => $password,
        ];
    }

    protected function bearerSanctum(string $token): array
    {
        return ['Authorization' => 'Bearer '.$token];
    }

    protected function firstProductIdFromCatalog(): ?int
    {
        $response = $this->getJson('/api/catalog');
        if ($response->status() !== 200) {
            return null;
        }

        $data = $response->json();
        if (! is_array($data)) {
            return null;
        }

        foreach ($data['categories'] ?? [] as $node) {
            foreach ($node['products'] ?? [] as $p) {
                if (isset($p['id'])) {
                    return (int) $p['id'];
                }
            }
        }

        return null;
    }

    /**
     * Контракт {@see \App\Application\Client\Presenter\ClientPresenter::present()}.
     *
     * @param  array<string, mixed>  $client
     */
    protected function assertClientPresenterContract(array $client): void
    {
        foreach ([
            'id', 'name', 'phone', 'email', 'birth_date', 'status',
            'consent_personal_data', 'consent_marketing', 'default_address_id',
            'addresses', 'created_at', 'updated_at',
        ] as $key) {
            $this->assertArrayHasKey($key, $client, 'Missing client key: '.$key);
        }

        $this->assertIsArray($client['addresses']);
        foreach ($client['addresses'] as $addr) {
            $this->assertIsArray($addr);
            foreach (['id', 'client_id', 'type', 'title', 'street', 'house', 'entrance', 'apartment', 'created_at', 'updated_at'] as $k) {
                $this->assertArrayHasKey($k, $addr, 'Missing address key: '.$k);
            }
        }
    }

    /**
     * Контракт {@see \App\Application\Order\Presenter\OrderPresenter::present()}.
     *
     * @param  array<string, mixed>  $order
     */
    protected function assertOrderPresenterContract(array $order): void
    {
        foreach ([
            'id', 'client_id', 'customer', 'status', 'subtotal', 'discount_total', 'total',
            'delivery', 'payment', 'items', 'created_at', 'updated_at',
        ] as $key) {
            $this->assertArrayHasKey($key, $order, 'Missing order key: '.$key);
        }

        $this->assertIsArray($order['customer']);
        foreach (['name', 'phone', 'email', 'address'] as $k) {
            $this->assertArrayHasKey($k, $order['customer']);
        }

        $this->assertIsArray($order['items']);
        foreach ($order['items'] as $item) {
            $this->assertIsArray($item);
            foreach (['id', 'order_id', 'product_original_id', 'product', 'quantity', 'unit_price', 'row_subtotal', 'row_discount', 'row_total'] as $ik) {
                $this->assertArrayHasKey($ik, $item);
            }
            $this->assertIsArray($item['product']);
            foreach (['name', 'sku', 'list_price', 'final_price', 'attributes', 'media'] as $pk) {
                $this->assertArrayHasKey($pk, $item['product']);
            }
        }
    }
}
