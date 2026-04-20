<?php

namespace Tests\Feature\Api;

final class CatalogApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessTablesExist([
            'PRD_categories',
            'PRD_products',
            'PRD_category_product',
        ]);
    }

    public function test_tree_200_and_category_product_contracts(): void
    {
        $response = $this->getJson('/api/catalog');
        $response->assertOk();

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertIsArray($data['categories']);

        foreach ($data['categories'] as $node) {
            $this->assertArrayHasKey('category', $node);
            $this->assertArrayHasKey('products', $node);
            $this->assertIsArray($node['products']);

            $cat = $node['category'];
            foreach (['id', 'name', 'slug', 'sort_order', 'is_active', 'created_at', 'updated_at'] as $k) {
                $this->assertArrayHasKey($k, $cat, 'category missing '.$k);
            }

            foreach ($node['products'] as $product) {
                foreach ([
                    'id', 'name', 'description', 'status', 'nutrition', 'images', 'ingredients',
                    'tags', 'cart_rule_flags', 'price', 'created_at', 'updated_at', 'archived_at',
                ] as $pk) {
                    $this->assertArrayHasKey($pk, $product, 'product missing '.$pk);
                }

                $this->assertIsArray($product['nutrition']);
                foreach (['calories', 'proteins', 'fats', 'carbs', 'basis'] as $nk) {
                    $this->assertArrayHasKey($nk, $product['nutrition']);
                }

                if ($product['price'] !== null) {
                    $this->assertIsNumeric($product['price']);
                }

                $this->assertIsArray($product['cart_rule_flags']);
                foreach (['counts_as_roll_unit', 'gift_candidate', 'is_complement_set_product'] as $fk) {
                    $this->assertArrayHasKey($fk, $product['cart_rule_flags'], 'cart_rule_flags missing '.$fk);
                    $this->assertIsBool($product['cart_rule_flags'][$fk]);
                }
            }
        }
    }
}
