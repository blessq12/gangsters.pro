<?php

namespace Tests\Feature\Catalog;

use Tests\ApiTestCase;

final class GetCatalogTest extends ApiTestCase
{
    public function test_catalog_returns_storefront_snapshot(): void
    {
        $response = $this->getJson('/api/catalog');

        $response->assertOk()
            ->assertJsonStructure([
                'categories',
                'accompanying_categories',
                'complement_products',
            ]);

        $this->assertNotEmpty($response->json('categories'));

        $firstCategory = $response->json('categories.0');
        $this->assertArrayHasKey('category', $firstCategory);
        $this->assertArrayHasKey('items', $firstCategory);
        $this->assertArrayHasKey('id', $firstCategory['category']);
        $this->assertArrayHasKey('name', $firstCategory['category']);
    }
}
