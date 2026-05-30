<?php

namespace Tests\Unit\Application\Site;

use App\Application\Site\SiteSeoResolver;
use PHPUnit\Framework\TestCase;

final class SiteSeoResolverTest extends TestCase
{
    public function test_resolve_for_delivery_path(): void
    {
        $resolver = new SiteSeoResolver(new InMemorySiteSeoPagesRepository([
            '/delivery' => [
                'title' => 'Доставка еды в Томске | Gangster\'s Sushi',
                'description' => 'Условия доставки',
                'robots' => 'index,follow',
            ],
        ]));
        $seo = $resolver->resolveForPath('delivery');

        $this->assertStringContainsString('Доставка еды в Томске', $seo['title']);
        $this->assertSame('index,follow', $seo['robots']);
    }

    public function test_normalize_empty_path_to_root(): void
    {
        $resolver = new SiteSeoResolver(new InMemorySiteSeoPagesRepository);

        $this->assertSame('/', $resolver->normalizePath(''));
        $this->assertSame('/', $resolver->normalizePath('/'));
    }

    public function test_indexable_paths_excludes_noindex_pages(): void
    {
        $resolver = new SiteSeoResolver(new InMemorySiteSeoPagesRepository([
            '/' => [
                'title' => 'Home',
                'description' => 'Home desc',
                'robots' => 'index,follow',
            ],
            '/delivery' => [
                'title' => 'Delivery',
                'description' => 'Delivery desc',
                'robots' => 'index,follow',
            ],
            '/reset-password' => [
                'title' => 'Reset',
                'description' => 'Reset desc',
                'robots' => 'noindex,nofollow',
            ],
        ]));
        $paths = $resolver->indexablePaths();

        $this->assertContains('/', $paths);
        $this->assertContains('/delivery', $paths);
        $this->assertNotContains('/reset-password', $paths);
    }

    public function test_invalidate_cache_reloads_pages(): void
    {
        $repository = new InMemorySiteSeoPagesRepository([
            '/about' => [
                'title' => 'Old',
                'description' => 'Old desc',
                'robots' => 'index,follow',
            ],
        ]);
        $resolver = new SiteSeoResolver($repository);

        $this->assertSame('Old', $resolver->resolveForPath('/about')['title']);

        $repository->save([
            '/about' => [
                'title' => 'New',
                'description' => 'New desc',
                'robots' => 'index,follow',
            ],
        ]);
        $resolver->invalidateCache();

        $this->assertSame('New', $resolver->resolveForPath('/about')['title']);
    }
}
