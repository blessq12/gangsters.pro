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

    public function test_use_order_store_is_isolated_to_order_feature(): void
    {
        $violations = [];
        $allowed = [
            $this->path('resources/js/features/orders/useOrderCommands.js'),
            $this->path('resources/js/features/orders/useOrdersReadModel.js'),
            $this->path('resources/js/features/checkout/useCheckout.js'),
            $this->path('resources/js/stores/orderStore.js'),
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
            "useOrderStore() только в order-feature и useCheckout:\n".implode("\n", $violations),
        );
    }

    public function test_use_checkout_intent_store_is_isolated_to_checkout_and_shopping(): void
    {
        $violations = [];
        $allowed = [
            $this->path('resources/js/features/checkout/useCheckout.js'),
            $this->path('resources/js/features/checkout/resetCheckoutAfterOrderCompleted.js'),
            $this->path('resources/js/features/orders/useOrderCommands.js'),
            $this->path('resources/js/features/shopping/shoppingApplySnapshot.js'),
            $this->path('resources/js/features/shopping/applyShoppingState.js'),
            $this->path('resources/js/stores/checkoutIntentStore.js'),
        ];

        foreach ($this->filesIn($this->path('resources/js'), '.js') as $file) {
            $code = (string) file_get_contents($file);
            if (str_contains($code, 'useCheckoutIntentStore(') && ! in_array($file, $allowed, true)) {
                $violations[] = $file;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "useCheckoutIntentStore() только в checkout/shopping:\n".implode("\n", $violations),
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
