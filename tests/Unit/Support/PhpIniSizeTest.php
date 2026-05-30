<?php

namespace Tests\Unit\Support;

use App\Support\PhpIniSize;
use PHPUnit\Framework\TestCase;

final class PhpIniSizeTest extends TestCase
{
    public function test_to_bytes_parses_megabytes(): void
    {
        $this->assertSame(2 * 1024 * 1024, PhpIniSize::toBytes('2M'));
    }

    public function test_to_bytes_parses_kilobytes(): void
    {
        $this->assertSame(512 * 1024, PhpIniSize::toBytes('512K'));
    }
}
