<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AppBootstrapSplitTest extends TestCase
{
    #[Test]
    public function critical_bootstrap_возвращает_ожидаемые_блоки(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/bootstrap/critical');

        $response->assertOk();
        $response->assertJsonStructure([
            'version',
            'catalog' => ['categories'],
            'promotion',
        ]);
    }

    #[Test]
    public function deferred_bootstrap_возвращает_ожидаемые_блоки(): void
    {
        if (! $this->databaseIsAvailable()) {
            $this->markTestSkipped('БД недоступна для feature-теста.');
        }

        $response = $this->getJson('/api/bootstrap/deferred');

        $response->assertOk();
        $response->assertJsonStructure([
            'catalog' => ['categories'],
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
