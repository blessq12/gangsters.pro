<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StorefrontBootstrapSplitTest extends TestCase
{
    #[Test]
    public function critical_bootstrap_возвращает_ожидаемые_блоки(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/storefront/bootstrap/critical');

        $response->assertOk();
        $response->assertJsonStructure([
            'version',
            'catalog' => ['categories'],
            'delivery',
            'promotion',
            'company' => ['main'],
            'marketing' => ['banners'],
        ]);
    }

    #[Test]
    public function deferred_bootstrap_возвращает_ожидаемые_блоки(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/storefront/bootstrap/deferred');

        $response->assertOk();
        $response->assertJsonStructure([
            'catalog' => ['categories'],
            'delivery' => ['zone'],
            'company' => ['legals', 'documents'],
            'marketing' => ['promotions'],
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
