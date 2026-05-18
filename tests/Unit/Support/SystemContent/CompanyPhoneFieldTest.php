<?php

namespace Tests\Unit\Support\SystemContent;

use App\Support\SystemContent\CompanyPhoneField;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CompanyPhoneFieldTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function test_normalize_russian_mobile(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, CompanyPhoneField::normalize($input));
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string}>
     */
    public static function normalizeProvider(): array
    {
        return [
            'empty' => [null, null],
            'formatted' => ['+7 (900) 123-45-67', '+7 (900) 123-45-67'],
            'digits_10' => ['9001234567', '+7 (900) 123-45-67'],
            'digits_11_7' => ['79001234567', '+7 (900) 123-45-67'],
        ];
    }
}
