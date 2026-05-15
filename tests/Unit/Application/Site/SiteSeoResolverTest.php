<?php

namespace Tests\Unit\Application\Site;

use App\Application\Site\SiteSeoResolver;
use Tests\TestCase;

final class SiteSeoResolverTest extends TestCase
{
    public function test_resolve_for_delivery_path(): void
    {
        $resolver = new SiteSeoResolver;
        $seo = $resolver->resolveForPath('delivery');

        $this->assertStringContainsString('Доставка еды в Томске', $seo['title']);
        $this->assertSame('index,follow', $seo['robots']);
    }

    public function test_normalize_empty_path_to_root(): void
    {
        $resolver = new SiteSeoResolver;

        $this->assertSame('/', $resolver->normalizePath(''));
        $this->assertSame('/', $resolver->normalizePath('/'));
    }

    public function test_indexable_paths_excludes_reset_password(): void
    {
        $resolver = new SiteSeoResolver;
        $paths = $resolver->indexablePaths();

        $this->assertContains('/', $paths);
        $this->assertContains('/delivery', $paths);
        $this->assertNotContains('/reset-password', $paths);
    }
}
