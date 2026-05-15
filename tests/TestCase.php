<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->assertRefreshDatabaseTraitIsNotUsed();

        parent::setUp();
    }

    /**
     * Запрет RefreshDatabase: тесты не должны дропать/мигрировать рабочую БД.
     */
    private function assertRefreshDatabaseTraitIsNotUsed(): void
    {
        $traits = class_uses_recursive(static::class);

        if (in_array(RefreshDatabase::class, $traits, true)) {
            throw new RuntimeException(
                'Трейт RefreshDatabase запрещён в тестах. Используй ApiTestCase::skipUnlessTablesExist() и точечные фикстуры.',
            );
        }
    }
}
