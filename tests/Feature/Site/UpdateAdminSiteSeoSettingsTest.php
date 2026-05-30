<?php

namespace Tests\Feature\Site;

use App\Application\Site\Command\UpdateAdminSiteSeoSettingsUseCase;
use App\Application\Site\DTO\SiteSeoPageEntryDto;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;
use App\Application\Site\SiteSeoResolver;
use Tests\TestCase;

final class UpdateAdminSiteSeoSettingsTest extends TestCase
{
    private string $seoPagesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPagesPath = storage_path('framework/testing/seo-pages-'.uniqid('', true).'.json');
        file_put_contents($this->seoPagesPath, json_encode([
            '/about' => [
                'title' => 'Старое about',
                'description' => 'Старое описание about',
                'robots' => 'index,follow',
            ],
        ], JSON_THROW_ON_ERROR));

        config(['site.seo_pages_path' => $this->seoPagesPath]);
        app(SiteSeoResolver::class)->invalidateCache();
    }

    protected function tearDown(): void
    {
        if (is_file($this->seoPagesPath)) {
            @unlink($this->seoPagesPath);
        }

        parent::tearDown();
    }

    public function test_update_reflects_in_resolver_for_path(): void
    {
        app(UpdateAdminSiteSeoSettingsUseCase::class)->execute(new UpdateSiteSeoSettingsDto([
            new SiteSeoPageEntryDto(
                path: '/about',
                title: 'Новый about title',
                description: 'Новое about description',
                robots: 'noindex,nofollow',
            ),
        ]));

        $seo = app(SiteSeoResolver::class)->resolveForPath('about');

        $this->assertSame('Новый about title', $seo['title']);
        $this->assertSame('Новое about description', $seo['description']);
        $this->assertSame('noindex,nofollow', $seo['robots']);
    }

    public function test_update_writes_json_atomically_to_configured_path(): void
    {
        app(UpdateAdminSiteSeoSettingsUseCase::class)->execute(new UpdateSiteSeoSettingsDto([
            new SiteSeoPageEntryDto(
                path: '/contacts',
                title: 'Контакты | Test',
                description: 'Описание контактов',
                robots: 'index,follow',
            ),
        ]));

        $decoded = json_decode((string) file_get_contents($this->seoPagesPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/contacts', $decoded);
        $this->assertSame('Контакты | Test', $decoded['/contacts']['title']);
    }
}
