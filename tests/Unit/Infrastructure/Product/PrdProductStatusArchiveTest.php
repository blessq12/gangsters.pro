<?php

namespace Tests\Unit\Infrastructure\Product;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PrdProductStatusArchiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('PRD_products')) {
            $this->markTestSkipped('Нет таблицы PRD_products.');
        }
    }

    public function test_archiving_sets_archived_at(): void
    {
        $product = PRD_Product::query()->create([
            'name' => 'Тест архивации '.uniqid(),
            'status' => ProductEntity::STATUS_ACTIVE,
        ]);

        $product->status = ProductEntity::STATUS_ARCHIVED;
        $product->save();

        $fresh = $product->fresh();
        $this->assertSame(ProductEntity::STATUS_ARCHIVED, $fresh->status);
        $this->assertNotNull($fresh->archived_at);

        $product->delete();
    }

    public function test_activating_clears_archived_at(): void
    {
        $product = PRD_Product::query()->create([
            'name' => 'Тест активации '.uniqid(),
            'status' => ProductEntity::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);

        $product->status = ProductEntity::STATUS_ACTIVE;
        $product->save();

        $fresh = $product->fresh();
        $this->assertSame(ProductEntity::STATUS_ACTIVE, $fresh->status);
        $this->assertNull($fresh->archived_at);

        $product->delete();
    }
}
