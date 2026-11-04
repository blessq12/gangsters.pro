<?php

namespace Tests\Unit\Catalog;

use App\Domain\Catalog\ValueObject\ProductImage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductImageFocalPointTest extends TestCase
{
    public function test_default_focus_is_center_when_null(): void
    {
        $image = new ProductImage(path: 'products/1/a.jpg', sortOrder: 0);

        $this->assertSame(50.0, $image->focusX());
        $this->assertSame(50.0, $image->focusY());
        $this->assertTrue($image->hasDefaultFocus());
        $this->assertSame('50% 50%', $image->objectPositionCss());
    }

    public function test_custom_focus_formats_object_position(): void
    {
        $image = new ProductImage(
            path: 'products/1/a.jpg',
            sortOrder: 0,
            focusX: 35.5,
            focusY: 60.0,
        );

        $this->assertSame(35.5, $image->focusX());
        $this->assertSame(60.0, $image->focusY());
        $this->assertFalse($image->hasDefaultFocus());
        $this->assertSame('35.5% 60%', $image->objectPositionCss());
    }

    public function test_partial_focus_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ProductImage(
            path: 'products/1/a.jpg',
            sortOrder: 0,
            focusX: 10.0,
            focusY: null,
        );
    }

    public function test_out_of_range_focus_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ProductImage(
            path: 'products/1/a.jpg',
            sortOrder: 0,
            focusX: -1.0,
            focusY: 50.0,
        );
    }
}
