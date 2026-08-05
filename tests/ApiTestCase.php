<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;

/**
 * База feature-тестов API: транзакции вместо RefreshDatabase, точечные фикстуры.
 */
abstract class ApiTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var list<string>
     */
    protected array $обязательныеТаблицы = [
        'CRM_clients',
        'ORD_orders',
        'PRD_products',
        'PRD_categories',
        'CMP_company',
        'DLV_configuration',
        'personal_access_tokens',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->пропуститьЕслиНетТаблиц($this->обязательныеТаблицы);
    }

    /**
     * @param  list<string>  $таблицы
     */
    protected function пропуститьЕслиНетТаблиц(array $таблицы): void
    {
        foreach ($таблицы as $таблица) {
            if (! Schema::hasTable($таблица)) {
                $this->markTestSkipped("Таблица «{$таблица}» отсутствует — пропуск feature-теста.");
            }
        }
    }

    protected function уникальныйТелефон(): string
    {
        // 9XX… — мобильный; иначе PhoneNumber срежет ведущую 7 как код страны ещё раз.
        $хвост = '9'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($хвост, 0, 3),
            substr($хвост, 3, 3),
            substr($хвост, 6, 2),
            substr($хвост, 8, 2),
        );
    }

    /**
     * @return array{token: string, client: array<string, mixed>, password: string, phone: string}
     */
    protected function зарегистрироватьКлиента(?string $телефон = null, ?string $пароль = null): array
    {
        $телефон ??= $this->уникальныйТелефон();
        $пароль ??= 'secret12';

        $ответ = $this->postJson('/api/client/register', [
            'name' => 'Тестовый Клиент',
            'phone' => $телефон,
            'email' => null,
            'password' => $пароль,
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $ответ->assertCreated()
            ->assertJsonStructure(['token', 'client' => ['id', 'phone', 'name']]);

        return [
            'token' => (string) $ответ->json('token'),
            'client' => $ответ->json('client'),
            'password' => $пароль,
            'phone' => $телефон,
        ];
    }

    protected function сТокеном(string $токен): static
    {
        return $this->withToken($токен);
    }

    protected function idАктивногоТовара(): int
    {
        $id = DB::table('PRD_products')
            ->where('status', 'active')
            ->where('is_system', 0)
            ->where('catalog_kind', 'product')
            ->orderBy('id')
            ->value('id');

        if ($id === null) {
            $this->markTestSkipped('Нет активного товара в каталоге.');
        }

        return (int) $id;
    }

    /**
     * Quote + place для зарегистрированного клиента (pickup — без геокодера).
     *
     * @return array{quote: array<string, mixed>, place: TestResponse, order_id: int}
     */
    protected function оформитьЗаказДляКлиента(string $токен, int $clientId, int $productId): array
    {
        $quoteОтвет = $this->postJson('/api/order/quote', [
            'lines' => [
                ['product_id' => $productId, 'quantity' => 1],
            ],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
            'client' => [
                'kind' => 'registered',
                'client_id' => $clientId,
            ],
        ]);

        $quoteОтвет->assertOk()->assertJsonStructure([
            'data' => ['cart', 'client', 'delivery', 'payment', 'totals'],
        ]);

        $quote = $quoteОтвет->json('data');
        $clientRequestId = 'phpunit-'.bin2hex(random_bytes(8));

        $placeОтвет = $this->postJson('/api/order/', [
            'client_request_id' => $clientRequestId,
            'cart' => $quote['cart'],
            'client' => $quote['client'],
            'delivery' => $quote['delivery'],
            'payment' => $quote['payment'],
        ]);

        $placeОтвет->assertCreated()
            ->assertJsonPath('data.client_request_id', $clientRequestId);

        return [
            'quote' => $quote,
            'place' => $placeОтвет,
            'order_id' => (int) $placeОтвет->json('data.id'),
        ];
    }
}
