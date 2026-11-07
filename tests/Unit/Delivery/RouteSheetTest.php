<?php

namespace Tests\Unit\Delivery;

use App\Domain\Delivery\Entity\RouteSheet;
use PHPUnit\Framework\TestCase;

final class RouteSheetTest extends TestCase
{
    public function test_courier_takes_a_sheet_marks_a_stop_and_completes_it(): void
    {
        $sheet = RouteSheet::release([10, 11])->take();
        $sheet = $sheet->settle(10, 'delivered', '');
        $sheet = $sheet->settle(11, 'failed', 'нет дома');
        $completed = $sheet->complete();

        $this->assertSame('completed', $completed->status());
        $this->assertSame('delivered', $completed->stops()[0]->outcome());
        $this->assertSame('нет дома', $completed->stops()[1]->reason());
    }

    public function test_complete_waits_until_every_stop_has_a_status(): void
    {
        $sheet = RouteSheet::release([10])->take();

        $this->expectException(\InvalidArgumentException::class);

        $sheet->complete();
    }
}
