<?php

namespace Tests\Domain\Production;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use PHPUnit\Framework\TestCase;

final class KitchenTicketAssigneeTest extends TestCase
{
    public function testСтартЗакрепляетПовара(): void
    {
        $ticket = KitchenTicket::queue(10, 2, [
            KitchenTicketLine::snapshot(1, 'Ролл', 1),
        ]);
        $ticket->assignId(5);
        $ticket->start(77, 2);

        $this->assertSame(KitchenTicketStatus::Started, $ticket->status());
        $this->assertSame(77, $ticket->assigneeEmployeeId());
    }

    public function testЧужойПоварМожетЗакрытьЛинииСвободныйПул(): void
    {
        $line = KitchenTicketLine::snapshot(1, 'Ролл', 1);
        $line->assignId(101);
        $ticket = KitchenTicket::queue(10, 2, [$line]);
        $ticket->assignId(5);
        $ticket->start(77, 2);

        $ticket->markLineDone(101, 99, 2);

        $this->assertSame(KitchenTicketStatus::Completed, $ticket->status());
        $this->assertSame(99, $ticket->lineById(101)->doneBy());
    }

    public function testCompleteПомещаетОставшиесяЛинииВDone(): void
    {
        $line = KitchenTicketLine::snapshot(1, 'Ролл', 1);
        $line->assignId(101);
        $ticket = KitchenTicket::queue(10, 2, [$line]);
        $ticket->assignId(5);
        $ticket->start(77, 2);
        $ticket->complete(77);

        $this->assertSame(KitchenTicketStatus::Completed, $ticket->status());
        $this->assertSame(77, $ticket->lineById(101)->doneBy());
    }
}
