<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FrontendAndAclBoundariesTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__, 3);
    }

    public function test_yandex_food_application_layer_has_no_direct_domain_imports(): void
    {
        $violations = [];
        foreach ($this->filesIn($this->path('app/Application/YandexFood'), '.php') as $file) {
            $code = (string) file_get_contents($file);
            if (preg_match('/^use\s+App\\\\Domain\\\\/m', $code) === 1) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "YandexFood application должен работать через ACL/контракты, без прямых Domain import:\n".implode("\n", $violations),
        );
    }

    public function test_use_order_store_is_isolated_to_order_feature_and_checkout_state(): void
    {
        $violations = [];
        $allowed = [
            $this->path('resources/js/composables/checkout/useCheckoutState.js'),
            $this->path('resources/js/features/orders/useOrderCommands.js'),
            $this->path('resources/js/features/orders/useOrdersReadModel.js'),
            $this->path('resources/js/features/shopping/shoppingApplySnapshot.js'),
        ];

        foreach ($this->filesIn($this->path('resources/js'), '.js') as $file) {
            $code = (string) file_get_contents($file);
            if (str_contains($code, 'useOrderStore(') && ! in_array($file, $allowed, true)) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "useOrderStore() должен использоваться только в order-feature/read-model и checkout-state:\n".implode("\n", $violations),
        );
    }

    /**
     * @return array<int, string>
     */
    private function filesIn(string $path, string $extension): array
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
            if (str_ends_with($fullPath, $extension)) {
                $result[] = $fullPath;
            }
        }

        return $result;
    }

    private function path(string $relative): string
    {
        return $this->projectRoot.'/'.ltrim($relative, '/');
    }
}
