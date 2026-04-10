<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_kopecks_to_api_rubles_rounds_to_two_decimals(): void
    {
        $this->assertEqualsWithDelta(12.34, Money::kopecksToApiRubles(1234), 0.00001);
        $this->assertEqualsWithDelta(0.01, Money::kopecksToApiRubles(1), 0.00001);
        $this->assertEqualsWithDelta(100.0, Money::kopecksToApiRubles(10000), 0.00001);
    }

    public function test_api_rubles_to_kopecks_parses_comma(): void
    {
        $this->assertSame(12345, Money::apiRublesToKopecks('123,45'));
        $this->assertSame(99, Money::apiRublesToKopecks(0.99));
        $this->assertNull(Money::apiRublesToKopecks(null));
        $this->assertNull(Money::apiRublesToKopecks(''));
    }

    public function test_format_rubles_ru_adaptive_omits_trailing_zero_cents(): void
    {
        $this->assertSame('150', Money::formatRublesRuAdaptive(150.0));
        $this->assertSame('149,90', Money::formatRublesRuAdaptive(149.9));
        $this->assertSame('1 234,56', Money::formatRublesRuAdaptive(1234.56));
        $this->assertSame('0,01', Money::formatRublesRuAdaptive(0.01));
    }

    public function test_format_api_rubles_always_two_fraction_digits(): void
    {
        $this->assertSame('150,00', Money::formatApiRubles(150.0));
        $this->assertSame('149,90', Money::formatApiRubles(149.9));
    }

    public function test_format_kopecks_for_admin_uses_adaptive(): void
    {
        $this->assertSame('100 ₽', Money::formatKopecksForAdmin(10000));
        $this->assertSame('12,34 ₽', Money::formatKopecksForAdmin(1234));
    }
}
