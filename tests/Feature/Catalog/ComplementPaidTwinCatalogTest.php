<?php

namespace Tests\Feature\Catalog;

use App\Infrastructure\Catalog\Model\PRD_Product;
use Illuminate\Support\Facades\Schema;
use Tests\ApiTestCase;

final class ComplementPaidTwinCatalogTest extends ApiTestCase
{
    public function test_complement_products_expose_paid_twin_and_hide_twin_from_menu(): void
    {
        if (! Schema::hasColumn('PRD_products', 'paid_twin_product_id')) {
            $this->markTestSkipped('Column paid_twin_product_id is missing — run migrations.');
        }

        $twin = PRD_Product::query()->create([
            'name' => 'Twin Paid '.bin2hex(random_bytes(3)),
            'slug' => 'twin-paid-'.bin2hex(random_bytes(3)),
            'sku' => 'TWIN-PAID-'.bin2hex(random_bytes(2)),
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => true,
            'price' => 150,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => false,
        ]);

        $complement = PRD_Product::query()->create([
            'name' => 'Complement Free '.bin2hex(random_bytes(3)),
            'slug' => 'complement-free-'.bin2hex(random_bytes(3)),
            'sku' => 'COMP-FREE-'.bin2hex(random_bytes(2)),
            'status' => 'active',
            'catalog_kind' => 'product',
            'is_system' => true,
            'price' => 0,
            'meta_counts_as_roll' => false,
            'meta_is_complement_set' => true,
            'paid_twin_product_id' => $twin->id,
        ]);

        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $complementProducts = $response->json('complement_products') ?? [];
        $match = null;
        foreach ($complementProducts as $row) {
            if ((int) ($row['id'] ?? 0) === (int) $complement->id) {
                $match = $row;
                break;
            }
        }

        $this->assertNotNull($match);
        $this->assertSame((int) $twin->id, (int) ($match['paid_twin_product_id'] ?? 0));
        $this->assertSame((int) $twin->id, (int) ($match['paid_twin']['id'] ?? 0));

        $menuIds = [];
        foreach ($response->json('categories') ?? [] as $node) {
            foreach ($node['items'] ?? [] as $item) {
                $menuIds[] = (int) ($item['id'] ?? 0);
            }
        }

        $this->assertNotContains((int) $twin->id, $menuIds);
        $this->assertNotContains((int) $complement->id, $menuIds);
    }
}
