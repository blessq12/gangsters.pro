<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CatalogReadTest extends TestCase
{
    #[Test]
    public function get_catalog_возвращает_200(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/catalog');

        $response->assertOk();
        $response->assertJsonStructure([
            'categories' => [
                '*' => [
                    'category',
                    'items' => [
                        '*' => [
                            'kind',
                            'id',
                            'images',
                        ],
                    ],
                ],
            ],
        ]);
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
