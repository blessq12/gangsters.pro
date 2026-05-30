<?php

namespace Tests\Feature\Admin;

use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Operations\Repository\EloquentAdminShoppingSessionReadRepository;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartLine;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentAdminShoppingSessionReadRepositoryTest extends TestCase
{
    /** @var list<int> */
    private array $createdSessionIds = [];

    /** @var list<int> */
    private array $createdClientIds = [];

    protected function tearDown(): void
    {
        if ($this->createdSessionIds !== [] && Schema::hasTable('SHP_shopping_cart_lines')) {
            SHP_ShoppingCartLine::query()
                ->whereIn('shopping_session_id', $this->createdSessionIds)
                ->delete();
        }

        if ($this->createdSessionIds !== [] && Schema::hasTable('SHP_shopping_sessions')) {
            SHP_ShoppingSession::query()
                ->whereIn('id', $this->createdSessionIds)
                ->delete();
        }

        if ($this->createdClientIds !== [] && Schema::hasTable('UR_clients')) {
            UR_Client::query()
                ->whereIn('id', $this->createdClientIds)
                ->forceDelete();
        }

        parent::tearDown();
    }

    public function test_paginate_active_carts_returns_only_non_expired_sessions_with_cart_lines(): void
    {
        $this->skipUnlessShoppingTablesExist();

        $activeWithCart = $this->createSession(expiresAt: now()->addDay());
        $this->addCartLine($activeWithCart, productId: 101);

        $activeEmpty = $this->createSession(expiresAt: now()->addDay());
        $expiredWithCart = $this->createSession(expiresAt: now()->subHour());
        $this->addCartLine($expiredWithCart, productId: 102);

        $result = app(EloquentAdminShoppingSessionReadRepository::class)->paginateActiveCarts(page: 1, perPage: 50);

        $ids = array_column($result['items'], 'id');

        $this->assertContains($activeWithCart, $ids);
        $this->assertNotContains($activeEmpty, $ids);
        $this->assertNotContains($expiredWithCart, $ids);
        $this->assertSame(1, $result['items'][0]['cart_lines_count']);
        $this->assertSame('Гость', $result['items'][0]['client_label']);
    }

    public function test_paginate_active_carts_respects_page_and_per_page(): void
    {
        $this->skipUnlessShoppingTablesExist();

        $repository = app(EloquentAdminShoppingSessionReadRepository::class);
        $baselineTotal = $repository->paginateActiveCarts(page: 1, perPage: 1)['total'];

        $oldest = $this->createSessionWithCart(
            expiresAt: now()->addDay(),
            updatedAt: now()->subHours(3),
        );
        $middle = $this->createSessionWithCart(
            expiresAt: now()->addDay(),
            updatedAt: now()->subHours(2),
        );
        $newest = $this->createSessionWithCart(
            expiresAt: now()->addDay(),
            updatedAt: now()->subHour(),
        );

        $expectedTotal = $baselineTotal + 3;
        $perPage = 2;

        $pageOne = $repository->paginateActiveCarts(page: 1, perPage: $perPage);
        $pageTwo = $repository->paginateActiveCarts(page: 2, perPage: $perPage);

        $this->assertSame($expectedTotal, $pageOne['total']);
        $this->assertCount(2, $pageOne['items']);
        $this->assertSame($expectedTotal, $pageTwo['total']);
        $this->assertGreaterThanOrEqual(1, count($pageTwo['items']));

        $allItems = $repository->paginateActiveCarts(page: 1, perPage: max($expectedTotal, 1))['items'];
        $createdIds = array_column(
            array_values(array_filter(
                $allItems,
                static fn (array $row): bool => in_array((int) $row['id'], [$oldest, $middle, $newest], true),
            )),
            'id',
        );

        $this->assertSame([$newest, $middle, $oldest], $createdIds);
    }

    public function test_paginate_active_carts_filters_by_session_id(): void
    {
        $this->skipUnlessShoppingTablesExist();

        $targetSession = $this->createSessionWithCart(expiresAt: now()->addDay(), updatedAt: now());
        $otherSession = $this->createSessionWithCart(expiresAt: now()->addDay(), updatedAt: now());

        $repository = app(EloquentAdminShoppingSessionReadRepository::class);
        $result = $repository->paginateActiveCarts(
            page: 1,
            perPage: 50,
            sessionId: $targetSession,
            clientId: null,
        );

        $ids = array_column($result['items'], 'id');

        $this->assertContains($targetSession, $ids);
        $this->assertNotContains($otherSession, $ids);
    }

    public function test_paginate_active_carts_filters_by_client_id(): void
    {
        $this->skipUnlessShoppingTablesExist();
        $this->skipUnlessClientTableExists();

        $clientId = $this->createClient();
        $targetSession = $this->createSession(expiresAt: now()->addDay(), clientId: $clientId);
        $this->addCartLine($targetSession, productId: 101);

        $otherSession = $this->createSessionWithCart(expiresAt: now()->addDay(), updatedAt: now());

        $repository = app(EloquentAdminShoppingSessionReadRepository::class);
        $result = $repository->paginateActiveCarts(
            page: 1,
            perPage: 50,
            clientId: $clientId,
            sessionId: null,
        );

        $ids = array_column($result['items'], 'id');

        $this->assertContains($targetSession, $ids);
        $this->assertNotContains($otherSession, $ids);
        $this->assertSame($clientId, $result['items'][0]['client_id']);
    }

    private function createSessionWithCart(
        \DateTimeInterface $expiresAt,
        \DateTimeInterface $updatedAt,
    ): int {
        $sessionId = $this->createSession($expiresAt);
        $this->addCartLine($sessionId, productId: 101);

        SHP_ShoppingSession::query()
            ->whereKey($sessionId)
            ->update(['updated_at' => $updatedAt]);

        return $sessionId;
    }

    private function createSession(\DateTimeInterface $expiresAt, ?int $clientId = null): int
    {
        $session = SHP_ShoppingSession::query()->create([
            'public_id' => (string) Str::uuid(),
            'client_id' => $clientId,
            'expires_at' => $expiresAt,
        ]);

        $this->createdSessionIds[] = (int) $session->id;

        return (int) $session->id;
    }

    private function addCartLine(int $sessionId, int $productId): void
    {
        SHP_ShoppingCartLine::query()->create([
            'shopping_session_id' => $sessionId,
            'product_id' => $productId,
            'quantity' => 1,
            'payload' => null,
        ]);
    }

    private function skipUnlessShoppingTablesExist(): void
    {
        foreach (['SHP_shopping_sessions', 'SHP_shopping_cart_lines'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped('Нет таблицы `'.$table.'` — выполни миграции на выбранной для тестов БД.');
            }
        }
    }

    private function skipUnlessClientTableExists(): void
    {
        if (! Schema::hasTable('UR_clients')) {
            $this->markTestSkipped('Нет таблицы `UR_clients` — выполни миграции на выбранной для тестов БД.');
        }
    }

    private function createClient(): int
    {
        $client = UR_Client::query()->create([
            'name' => 'Search Test Client',
            'phone' => '+7900'.random_int(1000000, 9999999),
            'email' => 'search-test-'.Str::uuid().'@example.test',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $this->createdClientIds[] = (int) $client->id;

        return (int) $client->id;
    }
}
