<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;

/**
 * Feature API test base: DatabaseTransactions instead of RefreshDatabase, point fixtures.
 */
abstract class ApiTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var list<string>
     */
    protected array $requiredTables = [
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

        $this->skipUnlessTablesExist($this->requiredTables);
    }

    /**
     * @param  list<string>  $tables
     */
    protected function skipUnlessTablesExist(array $tables): void
    {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped("Table \"{$table}\" is missing — skipping feature test.");
            }
        }
    }

    protected function uniquePhone(): string
    {
        // 9XX… mobile; otherwise PhoneNumber strips leading 7 as country code again.
        $digits = '9'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 2),
            substr($digits, 8, 2),
        );
    }

    /**
     * @return array{token: string, client: array<string, mixed>, password: string, phone: string}
     */
    protected function registerClient(?string $phone = null, ?string $password = null): array
    {
        $phone ??= $this->uniquePhone();
        $password ??= 'secret12';

        $response = $this->postJson('/api/client/register', [
            'name' => 'Test Client',
            'phone' => $phone,
            'email' => null,
            'password' => $password,
            'consent_personal_data' => true,
            'consent_marketing' => false,
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'client' => ['id', 'phone', 'name']]);

        return [
            'token' => (string) $response->json('token'),
            'client' => $response->json('client'),
            'password' => $password,
            'phone' => $phone,
        ];
    }

    protected function withBearer(string $token): static
    {
        return $this->withToken($token);
    }

    protected function activeProductId(): int
    {
        $id = DB::table('PRD_products')
            ->where('status', 'active')
            ->where('is_system', 0)
            ->where('catalog_kind', 'product')
            ->orderBy('id')
            ->value('id');

        if ($id === null) {
            $this->markTestSkipped('No active product in catalog.');
        }

        return (int) $id;
    }

    /**
     * Quote + place for a registered client (pickup — no geocoder).
     *
     * @return array{quote: array<string, mixed>, place: TestResponse, order_id: int}
     */
    protected function placeOrderForClient(string $token, int $clientId, int $productId): array
    {
        $quoteResponse = $this->postJson('/api/order/quote', [
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

        $quoteResponse->assertOk()->assertJsonStructure([
            'data' => ['cart', 'client', 'delivery', 'payment', 'totals'],
        ]);

        $quote = $quoteResponse->json('data');
        $clientRequestId = 'phpunit-'.bin2hex(random_bytes(8));

        $placeResponse = $this->postJson('/api/order/', [
            'client_request_id' => $clientRequestId,
            'cart' => $quote['cart'],
            'client' => $quote['client'],
            'delivery' => $quote['delivery'],
            'payment' => $quote['payment'],
        ]);

        $placeResponse->assertCreated()
            ->assertJsonPath('data.client_request_id', $clientRequestId);

        return [
            'quote' => $quote,
            'place' => $placeResponse,
            'order_id' => (int) $placeResponse->json('data.id'),
        ];
    }
}
