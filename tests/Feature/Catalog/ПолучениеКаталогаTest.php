<?php

namespace Tests\Feature\Catalog;

use Tests\ApiTestCase;

final class ПолучениеКаталогаTest extends ApiTestCase
{
    public function test_каталог_отдаёт_снимок_витрины(): void
    {
        $ответ = $this->getJson('/api/catalog');

        $ответ->assertOk()
            ->assertJsonStructure([
                'categories',
                'accompanying_categories',
                'complement_products',
            ]);

        $this->assertNotEmpty($ответ->json('categories'));

        $перваяКатегория = $ответ->json('categories.0');
        $this->assertArrayHasKey('category', $перваяКатегория);
        $this->assertArrayHasKey('items', $перваяКатегория);
        $this->assertArrayHasKey('id', $перваяКатегория['category']);
        $this->assertArrayHasKey('name', $перваяКатегория['category']);
    }
}
