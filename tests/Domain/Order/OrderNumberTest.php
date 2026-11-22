<?php

namespace Tests\Domain\Order;

use App\Domain\Core\Order\ValueObject\OrderNumber;
use PHPUnit\Framework\TestCase;

final class OrderNumberTest extends TestCase
{
    public function test_formats_padded_id_and_year(): void
    {
        $createdAt = new \DateTimeImmutable('2026-11-22T12:00:00+07:00');

        self::assertSame('ORD-0009-26', OrderNumber::format(9, $createdAt));
        self::assertSame('ORD-12345-26', OrderNumber::format(12345, $createdAt));
    }

    public function test_parses_public_number(): void
    {
        self::assertSame(
            ['id' => 9, 'year' => 26],
            OrderNumber::tryParse('ORD-0009-26'),
        );
        self::assertSame(
            ['id' => 9, 'year' => 26],
            OrderNumber::tryParse('ord-9-26'),
        );
        self::assertNull(OrderNumber::tryParse('9'));
        self::assertNull(OrderNumber::tryParse('ORD-0009'));
    }
}
