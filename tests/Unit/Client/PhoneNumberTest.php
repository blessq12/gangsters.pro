<?php

namespace Tests\Unit\Client;

use App\Domain\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PhoneNumberTest extends TestCase
{
    #[Test]
    public function format_from_raw_приводит_к_канону(): void
    {
        $this->assertSame(
            '+7 (999) 000-11-22',
            PhoneNumber::formatFromRaw('+79990001122'),
        );
        $this->assertSame(
            '+7 (999) 000-11-22',
            PhoneNumber::formatFromRaw('9990001122'),
        );
        $this->assertSame(
            '+7 (999) 000-11-22',
            PhoneNumber::formatFromRaw('+7 (999) 000-11-22'),
        );
    }

    #[Test]
    public function try_format_from_raw_возвращает_null_для_неполного_номера(): void
    {
        $this->assertNull(PhoneNumber::tryFormatFromRaw('9990001'));
    }
}
