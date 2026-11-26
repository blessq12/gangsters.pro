<?php

namespace Tests\Domain\Production;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketLineStatus;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use PHPUnit\Framework\TestCase;

final class KitchenTicketResyncTest extends TestCase
{
    public function test_resync_сбрасывает_готовность_и_переоткрывает_completed(): void
    {
        $old = KitchenTicketLine::snapshot(1, 'Ролл', 1);
        $old->assignId(10);
        $ticket = KitchenTicket::queue(5, 2, [$old]);
        $ticket->assignId(1);
        $ticket->markLineDone(10, 7, 2);
        $this->assertSame(KitchenTicketStatus::Completed, $ticket->status());

        $ticket->resyncProductionLines([
            KitchenTicketLine::snapshot(2, 'Суп', 2),
            KitchenTicketLine::snapshot(3, 'Ролл new', 1),
        ]);

        $this->assertSame(KitchenTicketStatus::Started, $ticket->status());
        $this->assertCount(2, $ticket->lines());
        foreach ($ticket->lines() as $line) {
            $this->assertSame(KitchenTicketLineStatus::Queued, $line->status());
            $this->assertFalse($line->hasId());
        }
    }
}
