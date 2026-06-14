<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

final class ArchitectureBoundariesTest extends TestCase
{
    private const DOMAIN_ROOT = 'app/Domain';

    private const FORBIDDEN_DOMAIN_IMPORTS = [
        'App\\Application\\',
        'App\\Infrastructure\\',
        'App\\Http\\',
        'App\\Filament\\',
        'Illuminate\\',
    ];

    private const KNOWN_DOMAIN_VIOLATIONS = [
        'app/Domain/Checkout/ValueObject/CheckoutId.php → Illuminate\Support\Str',
    ];

    private const KNOWN_APPLICATION_VIOLATIONS = [];

    private const FORBIDDEN_APPLICATION_IMPORTS = [
        'App\\Infrastructure\\',
        'App\\Http\\',
        'App\\Filament\\',
    ];

    #[Test]
    public function domain_не_импортирует_внешние_слои(): void
    {
        $violations = $this->collectImportViolations(
            root: self::DOMAIN_ROOT,
            forbiddenPrefixes: self::FORBIDDEN_DOMAIN_IMPORTS,
        );

        $crossContextViolations = $this->collectCrossContextDomainImports();

        $this->assertSame(
            [],
            array_values(array_diff(
                array_merge($violations, $crossContextViolations),
                self::KNOWN_DOMAIN_VIOLATIONS,
            )),
            "Новые нарушения границ Domain:\n".implode("\n", array_diff(
                array_merge($violations, $crossContextViolations),
                self::KNOWN_DOMAIN_VIOLATIONS,
            )),
        );
    }

    #[Test]
    public function application_не_импортирует_infrastructure_и_http(): void
    {
        $violations = $this->collectImportViolations(
            root: 'app/Application',
            forbiddenPrefixes: self::FORBIDDEN_APPLICATION_IMPORTS,
        );

        $this->assertSame(
            [],
            array_values(array_diff($violations, self::KNOWN_APPLICATION_VIOLATIONS)),
            "Новые нарушения границ Application:\n".implode("\n", array_diff(
                $violations,
                self::KNOWN_APPLICATION_VIOLATIONS,
            )),
        );
    }

    /**
     * @param  list<string>  $forbiddenPrefixes
     * @return list<string>
     */
    private function collectImportViolations(string $root, array $forbiddenPrefixes): array
    {
        $violations = [];
        $basePath = base_path($root);

        foreach ($this->phpFiles($basePath) as $file) {
            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            if (! preg_match_all('/^use\s+([^;]+);/m', $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $import) {
                $import = trim($import);

                foreach ($forbiddenPrefixes as $prefix) {
                    if (str_starts_with($import, $prefix)) {
                        $violations[] = sprintf('%s → %s', $this->relativePath($file), $import);
                    }
                }
            }
        }

        sort($violations);

        return $violations;
    }

    /**
     * @return list<string>
     */
    private function collectCrossContextDomainImports(): array
    {
        $violations = [];
        $basePath = base_path(self::DOMAIN_ROOT);

        foreach ($this->phpFiles($basePath) as $file) {
            $relative = $this->relativePath($file);
            $ownContext = $this->resolveDomainContext($relative);

            if ($ownContext === null) {
                continue;
            }

            $contents = file_get_contents($file);
            if ($contents === false || ! preg_match_all('/^use\s+App\\\\Domain\\\\([^;]+);/m', $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $importSuffix) {
                $importContext = explode('\\', $importSuffix)[0] ?? null;

                if ($importContext !== null && $importContext !== $ownContext) {
                    $violations[] = sprintf(
                        '%s → App\\Domain\\%s',
                        $relative,
                        $importSuffix,
                    );
                }
            }
        }

        sort($violations);

        return $violations;
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
        );
        $regex = new RegexIterator($iterator, '/\.php$/');

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

    private function resolveDomainContext(string $relativePath): ?string
    {
        if (! preg_match('#^app/Domain/([^/]+)/#', $relativePath, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
