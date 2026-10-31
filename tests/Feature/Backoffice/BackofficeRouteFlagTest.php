<?php

namespace Tests\Feature\Backoffice;

use Tests\TestCase;

final class BackofficeRouteFlagTest extends TestCase
{
    public function test_backoffice_returns_not_found_when_disabled(): void
    {
        config(['backoffice.enabled' => false]);

        $this->get('/backoffice')->assertNotFound();
        $this->get('/backoffice/administrator/catalog')->assertNotFound();
    }

    public function test_backoffice_returns_ok_when_enabled(): void
    {
        config(['backoffice.enabled' => true]);

        $this->get('/backoffice')
            ->assertOk()
            ->assertViewIs('backoffice');
    }
}
