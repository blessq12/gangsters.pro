<?php

namespace Tests\Feature\Content;

use Tests\ApiTestCase;

final class GetBootstrapTest extends ApiTestCase
{
    public function test_content_returns_bootstrap_snapshot(): void
    {
        $response = $this->getJson('/api/content');

        $response->assertOk()
            ->assertJsonStructure([
                'version',
                'company' => [
                    'main',
                    'legals',
                    'documents',
                ],
                'marketing' => [
                    'banners',
                    'promotions',
                ],
                'delivery',
            ]);

        $this->assertNotNull($response->json('company.main'));
        $this->assertNotNull($response->json('delivery'));
        $this->assertArrayHasKey('settings', $response->json('delivery'));
        $this->assertArrayHasKey('zone', $response->json('delivery'));
    }
}
