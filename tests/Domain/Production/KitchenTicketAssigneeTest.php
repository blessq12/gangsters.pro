<?php

namespace Tests\Domain\Production;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use PHPUnit\Framework\TestCase;

final class KitchenTicketAssigneeTest extends TestCase
{
    public function test_старт_закрепляет_повара(): void
    {
        $ticket = KitchenTicket::queue(10, 2, [
            KitchenTicketLine::snapshot(1, 'Ролл', 1),
        ]);
        $ticket->assignId(5);
        $ticket->start(77, 2);

        $this->assertSame(KitchenTicketStatus::Started, $ticket->status());
        $this->assertSame(77, $ticket->assigneeEmployeeId());
    }

    public function test_чужой_повар_может_закрыть_линии_свободный_пул(): void
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

    public function test_complete_помещает_оставшиеся_линии_в_done(): void
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
