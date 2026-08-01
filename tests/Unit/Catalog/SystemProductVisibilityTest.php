<?php

namespace Tests\Unit\Catalog;

use App\Application\Catalog\useCases\GetCatalogUseCase;
use App\Application\Order\OrderDraft\DTO\OrderDraftInput;
use App\Application\Order\OrderDraft\Services\BuildOrderDraftFromInput;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_CategoryProduct;
use App\Infrastructure\Catalog\Model\PRD_Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SystemProductVisibilityTest extends TestCase
{
    #[Test]
    public function кандидаты_подарка_только_системные_товары(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для unit-теста.');
        }

        $fixture = $this->seedSystemProductFixture();
        $candidates = app(\App\Infrastructure\Order\Port\CatalogGiftCandidatesAdapter::class)
            ->listActiveGiftCandidates();
        $candidateIds = array_map(
            static fn (\App\Domain\Order\Port\CatalogGiftCandidate $candidate): int => $candidate->productId(),
            $candidates,
        );

        $this->assertContains($fixture['system_product_id'], $candidateIds);
        $this->assertNotContains($fixture['public_product_id'], $candidateIds);

        $this->cleanupFixture($fixture);
    }

    #[Test]
    public function системный_товар_не_попадает_в_публичный_каталог(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для unit-теста.');
        }

        $fixture = $this->seedSystemProductFixture();
        $catalog = app(GetCatalogUseCase::class)->execute();

        $itemIds = [];

        foreach ($catalog['categories'] as $categoryNode) {
            foreach ($categoryNode['items'] as $item) {
                $itemIds[] = (int) $item['id'];
            }
        }

        $this->assertNotContains($fixture['system_product_id'], $itemIds);
        $this->assertContains($fixture['public_product_id'], $itemIds);

        $this->cleanupFixture($fixture);
    }

    #[Test]
    public function системный_товар_нельзя_добавить_в_пользовательскую_корзину(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для unit-теста.');
        }

        $fixture = $this->seedSystemProductFixture();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Товар недоступен для добавления в корзину.');

        try {
            app(BuildOrderDraftFromInput::class)->build(new OrderDraftInput(
                cartLines: [
                    ['product_id' => $fixture['system_product_id'], 'quantity' => 1],
                ],
                selectedGiftProductId: null,
                client: null,
                delivery: null,
                payment: null,
            ));
        } finally {
            $this->cleanupFixture($fixture);
        }
    }

    /**
     * @return array{
     *     category_id: int,
     *     system_product_id: int,
     *     public_product_id: int,
     *     system_slug: string,
     *     public_slug: string
     * }
     */
    private function seedSystemProductFixture(): array
    {
        $suffix = Str::lower(Str::random(8));
        $systemSlug = "system-product-{$suffix}";
        $publicSlug = "public-product-{$suffix}";
        $categorySlug = "system-fixture-category-{$suffix}";

        $category = PRD_Category::query()->create([
            'name' => "Fixture {$suffix}",
            'slug' => $categorySlug,
            'sort_order' => 99_999,
            'is_active' => true,
        ]);

        $systemProduct = PRD_Product::query()->create([
            'name' => "Системный {$suffix}",
            'slug' => $systemSlug,
            'status' => ProductStatus::Active->value,
            'catalog_kind' => CatalogItemKind::Product->value,
            'is_system' => true,
            'price' => 100,
        ]);

        $publicProduct = PRD_Product::query()->create([
            'name' => "Витринный {$suffix}",
            'slug' => $publicSlug,
            'status' => ProductStatus::Active->value,
            'catalog_kind' => CatalogItemKind::Product->value,
            'is_system' => false,
            'price' => 200,
        ]);

        foreach ([$systemProduct, $publicProduct] as $index => $product) {
            PRD_CategoryProduct::query()->create([
                'category_id' => $category->id,
                'product_id' => $product->id,
                'sort_order' => $index,
            ]);
        }

        return [
            'category_id' => (int) $category->id,
            'system_product_id' => (int) $systemProduct->id,
            'public_product_id' => (int) $publicProduct->id,
            'system_slug' => $systemSlug,
            'public_slug' => $publicSlug,
        ];
    }

    /**
     * @param  array{
     *     category_id: int,
     *     system_product_id: int,
     *     public_product_id: int,
     *     system_slug: string,
     *     public_slug: string
     * }  $fixture
     */
    private function cleanupFixture(array $fixture): void
    {
        PRD_CategoryProduct::query()
            ->where('category_id', $fixture['category_id'])
            ->delete();

        PRD_Product::query()
            ->whereIn('id', [$fixture['system_product_id'], $fixture['public_product_id']])
            ->delete();

        PRD_Category::query()
            ->where('id', $fixture['category_id'])
            ->delete();
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
