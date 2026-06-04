<?php

namespace Tests\Feature\Admin;

use App\Filament\Support\AdminActiveCartSnapshotBuilder;
use App\Filament\Operations\Support\FilamentActiveCartDetailMapper;
use App\Infrastructure\Shopping\Model\SHP_ShoppingCartLine;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class HubActiveCartsTableViewActionTest extends TestCase
{
    /** @var list<int> */
    private array $createdSessionIds = [];

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

        parent::tearDown();
    }

    public function test_detail_form_state_includes_cart_lines_for_active_session(): void
    {
        $this->skipUnlessTablesExist();

        $sessionId = $this->createSession(expiresAt: now()->addDay());
        $this->addCartLine($sessionId, productId: 101, quantity: 2);

        $snapshot = app(AdminActiveCartSnapshotBuilder::class)->build($sessionId);
        $state = FilamentActiveCartDetailMapper::toFormState($snapshot);

        $this->assertSame('1 поз. · 2 шт.', $state['cart_summary']);
        $this->assertCount(1, $state['cart_lines']);
        $this->assertSame(101, $state['cart_lines'][0]['product_id']);
        $this->assertNotEmpty($state['cart_lines'][0]['product_name']);
        $this->assertSame(2, $state['cart_lines'][0]['quantity']);
    }

    private function createSession(\DateTimeInterface $expiresAt): int
    {
        $session = SHP_ShoppingSession::query()->create([
            'public_id' => (string) Str::uuid(),
            'client_id' => null,
            'expires_at' => $expiresAt,
        ]);

        $this->createdSessionIds[] = (int) $session->id;

        return (int) $session->id;
    }

    private function addCartLine(int $sessionId, int $productId, int $quantity = 1): void
    {
        SHP_ShoppingCartLine::query()->create([
            'shopping_session_id' => $sessionId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'payload' => null,
        ]);
    }

    private function skipUnlessTablesExist(): void
    {
        foreach (['SHP_shopping_sessions', 'SHP_shopping_cart_lines'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped('Нет таблицы `'.$table.'` — выполни миграции на выбранной для тестов БД.');
            }
        }
    }
}
