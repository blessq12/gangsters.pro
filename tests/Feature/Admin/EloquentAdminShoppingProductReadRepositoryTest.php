<?php

namespace Tests\Feature\Admin;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Operations\Repository\EloquentAdminShoppingProductReadRepository;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EloquentAdminShoppingProductReadRepositoryTest extends TestCase
{
    /** @var list<int> */
    private array $createdProductIds = [];

    protected function tearDown(): void
    {
        if ($this->createdProductIds !== [] && Schema::hasTable('PRD_products')) {
            PRD_Product::query()->whereIn('id', $this->createdProductIds)->delete();
        }

        parent::tearDown();
    }

    public function test_find_summaries_by_ids_returns_only_requested_fields(): void
    {
        $this->skipUnlessProductsTableExists();

        $product = PRD_Product::query()->create([
            'name' => 'Ops cart summary '.uniqid(),
            'status' => ProductEntity::STATUS_ACTIVE,
            'price' => 12345,
        ]);
        $this->createdProductIds[] = (int) $product->id;

        $summaries = app(EloquentAdminShoppingProductReadRepository::class)->findSummariesByIds([
            (int) $product->id,
            999999,
        ]);

        $this->assertArrayHasKey((int) $product->id, $summaries);
        $this->assertSame((int) $product->id, $summaries[(int) $product->id]['id']);
        $this->assertSame(12345, $summaries[(int) $product->id]['price_kopecks']);
        $this->assertSame(ProductEntity::STATUS_ACTIVE, $summaries[(int) $product->id]['status']);
        $this->assertArrayNotHasKey(999999, $summaries);
    }

    public function test_find_summaries_by_ids_returns_empty_for_empty_input(): void
    {
        $this->skipUnlessProductsTableExists();

        $summaries = app(EloquentAdminShoppingProductReadRepository::class)->findSummariesByIds([]);

        $this->assertSame([], $summaries);
    }

    private function skipUnlessProductsTableExists(): void
    {
        if (! Schema::hasTable('PRD_products')) {
            $this->markTestSkipped('Нет таблицы `PRD_products` — выполни миграции на выбранной для тестов БД.');
        }
    }
}
