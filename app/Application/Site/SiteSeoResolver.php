<?php

namespace App\Application\Site;

use App\Application\Site\Contracts\SiteSeoPagesRepository;

final class SiteSeoResolver
{
    /** @var array<string, array<string, string>>|null */
    private ?array $pages = null;

    public function __construct(
        private readonly SiteSeoPagesRepository $pagesRepository,
    ) {}

    public function invalidateCache(): void
    {
        $this->pages = null;
    }

    public function normalizePath(string $path): string
    {
        $trimmed = trim($path, '/');

        return $trimmed === '' ? '/' : '/'.$trimmed;
    }

    /**
     * @return array{title: string, description: string, robots: string}
     */
    public function resolveForPath(string $path): array
    {
        $key = $this->normalizePath($path);
        $pages = $this->pages();
        $page = $pages[$key] ?? null;

        return [
            'title' => (string) ($page['title'] ?? config('site.default_title')),
            'description' => (string) ($page['description'] ?? config('site.default_description')),
            'robots' => (string) ($page['robots'] ?? 'index,follow'),
        ];
    }

    /**
     * @return list<string>
     */
    public function indexablePaths(): array
    {
        $paths = [];
        foreach ($this->pages() as $path => $page) {
            $robots = (string) ($page['robots'] ?? 'index,follow');
            if (stripos($robots, 'noindex') !== false) {
                continue;
            }
            $paths[] = $path;
        }

        sort($paths);

        return $paths;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function pages(): array
    {
        if ($this->pages !== null) {
            return $this->pages;
        }

        $this->pages = $this->pagesRepository->all();

        return $this->pages;
    }
}
