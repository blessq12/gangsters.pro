<?php

namespace App\Infrastructure\Site\Repository;

use App\Application\Site\Contracts\SiteSeoPagesRepository;
use RuntimeException;

final class JsonSiteSeoPagesRepository implements SiteSeoPagesRepository
{
    public function all(): array
    {
        $path = $this->filePath();
        if ($path === '' || ! is_readable($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $this->normalizeStoredPages($decoded) : [];
    }

    public function save(array $pages): void
    {
        $path = $this->filePath();
        if ($path === '') {
            throw new RuntimeException('SEO pages path is not configured.');
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Cannot create directory: {$directory}");
        }

        $payload = json_encode(
            $pages,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        $temporaryPath = tempnam($directory, 'seo-pages-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Cannot create temporary SEO pages file.');
        }

        try {
            if (file_put_contents($temporaryPath, $payload) === false) {
                throw new RuntimeException('Cannot write temporary SEO pages file.');
            }

            if (! rename($temporaryPath, $path)) {
                throw new RuntimeException('Cannot replace SEO pages file.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    private function filePath(): string
    {
        return (string) config('site.seo_pages_path');
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @return array<string, array{title: string, description: string, robots: string}>
     */
    private function normalizeStoredPages(array $decoded): array
    {
        $pages = [];

        foreach ($decoded as $path => $page) {
            if (! is_string($path) || ! is_array($page)) {
                continue;
            }

            $pages[$path] = [
                'title' => (string) ($page['title'] ?? ''),
                'description' => (string) ($page['description'] ?? ''),
                'robots' => (string) ($page['robots'] ?? 'index,follow'),
            ];
        }

        return $pages;
    }
}
