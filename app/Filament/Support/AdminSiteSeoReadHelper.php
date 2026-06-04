<?php

namespace App\Filament\Support;

use App\Infrastructure\Site\Repository\JsonSiteSeoPagesRepository;

final class AdminSiteSeoReadHelper
{
    /**
     * @return array{
     *     default_title: string,
     *     default_description: string,
     *     pages: list<array{path: string, title: string, description: string, robots: string}>
     * }
     */
    public function settingsState(): array
    {
        $rows = [];

        foreach (app(JsonSiteSeoPagesRepository::class)->all() as $path => $page) {
            $rows[] = [
                'path' => $path,
                'title' => (string) ($page['title'] ?? ''),
                'description' => (string) ($page['description'] ?? ''),
                'robots' => (string) ($page['robots'] ?? 'index,follow'),
            ];
        }

        usort($rows, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));

        return [
            'default_title' => (string) config('site.default_title'),
            'default_description' => (string) config('site.default_description'),
            'pages' => $rows,
        ];
    }
}
