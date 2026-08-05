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
     * Forbid RefreshDatabase: tests must not drop/migrate the working database.
     */
    private function assertRefreshDatabaseTraitIsNotUsed(): void
    {
        $traits = class_uses_recursive(static::class);

        if (in_array(RefreshDatabase::class, $traits, true)) {
            throw new RuntimeException(
                'RefreshDatabase trait is forbidden. Use ApiTestCase::skipUnlessTablesExist() and point fixtures.',
            );
        }
    }
}
