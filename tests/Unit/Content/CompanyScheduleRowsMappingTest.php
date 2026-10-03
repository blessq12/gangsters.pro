<?php

namespace Tests\Unit\Content;

use App\Filament\Content\Company\Resources\CompanyResource\Pages\ManageCompany;
use App\Infrastructure\Content\Model\CMP_Company;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class CompanyScheduleRowsMappingTest extends TestCase
{
    public function test_schedule_rows_map_by_index_when_day_missing(): void
    {
        $page = new ManageCompany;
        $method = new ReflectionMethod(ManageCompany::class, 'scheduleRowsToObject');
        $method->setAccessible(true);

        $rows = [
            'uuid-a' => ['work' => '11:00-21:00', 'is_day_off' => false],
            'uuid-b' => ['work' => '12:00-20:00', 'is_day_off' => false],
            'uuid-c' => ['work' => null, 'is_day_off' => true],
            'uuid-d' => ['work' => '10:00-18:00', 'is_day_off' => false],
            'uuid-e' => ['work' => '10:00-18:00', 'is_day_off' => false],
            'uuid-f' => ['work' => '10:00-18:00', 'is_day_off' => false],
            'uuid-g' => ['work' => null, 'is_day_off' => true],
        ];

        /** @var array<string, array{work: ?string, is_day_off: bool}> $result */
        $result = $method->invoke($page, $rows);

        $this->assertSame('11:00-21:00', $result['mon']['work']);
        $this->assertSame('12:00-20:00', $result['tue']['work']);
        $this->assertTrue($result['wed']['is_day_off']);
        $this->assertNull($result['wed']['work']);
        $this->assertTrue($result['sun']['is_day_off']);
    }

    public function test_schedule_rows_prefer_day_key_when_present(): void
    {
        $page = new ManageCompany;
        $method = new ReflectionMethod(ManageCompany::class, 'scheduleRowsToObject');
        $method->setAccessible(true);

        $rows = [
            ['day' => 'fri', 'work' => '09:00-17:00', 'is_day_off' => false],
        ];

        /** @var array<string, array{work: ?string, is_day_off: bool}> $result */
        $result = $method->invoke($page, $rows);

        $this->assertSame('09:00-17:00', $result['fri']['work']);
        $this->assertFalse($result['fri']['is_day_off']);
    }
}
