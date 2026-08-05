<?php

namespace Tests\Feature\Content;

use Tests\ApiTestCase;

final class ПолучениеBootstrapTest extends ApiTestCase
{
    public function test_content_отдаёт_bootstrap_снимок(): void
    {
        $ответ = $this->getJson('/api/content');

        $ответ->assertOk()
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

        $this->assertNotNull($ответ->json('company.main'));
        $this->assertNotNull($ответ->json('delivery'));
        $this->assertArrayHasKey('settings', $ответ->json('delivery'));
        $this->assertArrayHasKey('zone', $ответ->json('delivery'));
    }
}
