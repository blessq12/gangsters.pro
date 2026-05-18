<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ArchitectureBoundariesTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__, 3);
    }

    public function test_domain_and_application_layers_do_not_depend_on_infrastructure_namespace(): void
    {
        $violations = [];

        foreach ($this->phpFilesIn($this->path('app/Domain')) as $file) {
            $code = (string) file_get_contents($file);
            if ($this->containsForbiddenInfrastructureReference($code)) {
                $violations[] = $file;
            }
        }

        foreach ($this->phpFilesIn($this->path('app/Application')) as $file) {
            $code = (string) file_get_contents($file);
            if ($this->containsForbiddenInfrastructureReference($code)) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Domain/Application не должны импортировать Infrastructure:\n".implode("\n", $violations),
        );
    }

    public function test_api_controllers_do_not_depend_on_infrastructure_models(): void
    {
        $violations = [];

        foreach ($this->phpFilesIn($this->path('app/Http/Controllers/Api')) as $file) {
            $code = (string) file_get_contents($file);
            if ($this->containsForbiddenInfrastructureReference($code)) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "API controllers не должны зависеть от Infrastructure namespace:\n".implode("\n", $violations),
        );
    }

    public function test_http_middleware_do_not_depend_on_infrastructure_models(): void
    {
        $violations = [];

        foreach ($this->phpFilesIn($this->path('app/Http/Middleware')) as $file) {
            $code = (string) file_get_contents($file);
            if ($this->containsForbiddenInfrastructureReference($code)) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "HTTP middleware не должны зависеть от Infrastructure namespace:\n".implode("\n", $violations),
        );
    }

    public function test_http_controllers_do_not_read_env_directly(): void
    {
        $violations = [];

        foreach ($this->phpFilesIn($this->path('app/Http/Controllers')) as $file) {
            $code = (string) file_get_contents($file);
            if (str_contains($code, 'env(')) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "HTTP controllers не должны читать env() напрямую:\n".implode("\n", $violations),
        );
    }

    public function test_legacy_frontpad_provider_and_facades_are_removed(): void
    {
        $this->assertFileDoesNotExist($this->path('app/Providers/FrontPadServiceProvider.php'));
        $this->assertFileDoesNotExist($this->path('app/Facades/Frontpad.php'));
        $this->assertFileDoesNotExist($this->path('app/Facades/YaMetrika.php'));
    }

    public function test_order_catalog_snapshot_contract_is_single_source_of_truth(): void
    {
        $this->assertFileDoesNotExist($this->path('app/Application/Order/Contracts/CatalogItemSnapshotProvider.php'));
        $this->assertFileExists($this->path('app/Domain/Order/Contracts/CatalogItemSnapshotProvider.php'));
    }

    public function test_legacy_console_commands_live_under_legacy_namespace(): void
    {
        $this->assertFileExists($this->path('app/Console/Commands/Legacy/MigrateLegacyDomainsCommand.php'));
        $this->assertFileExists($this->path('app/Console/Commands/Legacy/DropLegacyTablesCommand.php'));
        $this->assertSame([], $this->phpFilesIn($this->path('app/Legacy/Console/Commands')));
    }

    /**
     * @return array<int, string>
     */
    private function phpFilesIn(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $result = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($it as $file) {
            $fullPath = $file->getPathname();
            if (str_ends_with($fullPath, '.php')) {
                $result[] = $fullPath;
            }
        }

        return $result;
    }

    private function path(string $relative): string
    {
        return $this->projectRoot.'/'.ltrim($relative, '/');
    }

    private function containsForbiddenInfrastructureReference(string $code): bool
    {
        $codeWithoutComments = preg_replace('#//.*$#m', '', $code) ?? $code;
        $codeWithoutComments = preg_replace('#/\*.*?\*/#s', '', $codeWithoutComments) ?? $codeWithoutComments;

        return preg_match('/(?:^|[^A-Za-z0-9_])\\\\?App\\\\Infrastructure\\\\/m', $codeWithoutComments) === 1;
    }
}
