<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

final class FrontendAndAclBoundariesTest extends TestCase
{
    private const FORBIDDEN_FRONTEND_IMPORTS = [
        'features/shopping/',
        'api/shoppingApi',
        'stores/cartStore',
        'stores/checkoutIntentStore',
        'features/orders/useOrderCommands',
    ];

    #[Test]
    public function frontend_не_содержит_legacy_shopping_контур(): void
    {
        $violations = [];
        $basePath = base_path('resources/js');

        foreach ($this->jsFiles($basePath) as $file) {
            $relative = $this->relativePath($file);
            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            foreach (self::FORBIDDEN_FRONTEND_IMPORTS as $forbidden) {
                if (str_contains($contents, $forbidden)) {
                    $violations[] = sprintf('%s содержит %s', $relative, $forbidden);
                }
            }
        }

        $deadPaths = [
            'resources/js/features/shopping',
            'resources/js/api/shoppingApi.js',
            'resources/js/stores/cartStore.js',
            'resources/js/stores/checkoutIntentStore.js',
            'resources/js/features/orders/useOrderCommands.js',
        ];

        foreach ($deadPaths as $path) {
            if (file_exists(base_path($path))) {
                $violations[] = sprintf('Мёртвый файл всё ещё существует: %s', $path);
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Нарушения frontend boundaries:\n".implode("\n", $violations),
        );
    }

    /**
     * @return list<string>
     */
    private function jsFiles(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
        );
        $regex = new RegexIterator($iterator, '/\.(js|vue)$/');

        $files = [];
        foreach ($regex as $file) {
            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    private function relativePath(string $absolutePath): string
    {
        return ltrim(str_replace(base_path().'/', '', $absolutePath), '/');
    }
}
