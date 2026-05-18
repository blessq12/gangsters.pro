<?php

namespace Tests\Unit\Support\Product;

use App\Domain\Product\Entity\Product;
use App\Support\Product\ProductStatusLabels;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ProductStatusLabelsTest extends TestCase
{
    public function test_options_cover_both_statuses(): void
    {
        $this->assertArrayHasKey(Product::STATUS_ACTIVE, ProductStatusLabels::options());
        $this->assertArrayHasKey(Product::STATUS_ARCHIVED, ProductStatusLabels::options());
    }

    #[DataProvider('labelProvider')]
    public function test_label_and_color(string $status, string $label, string $color): void
    {
        $this->assertSame($label, ProductStatusLabels::label($status));
        $this->assertSame($color, ProductStatusLabels::color($status));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function labelProvider(): array
    {
        return [
            'active' => [Product::STATUS_ACTIVE, 'Активен', 'success'],
            'archived' => [Product::STATUS_ARCHIVED, 'Архив', 'gray'],
        ];
    }
}
