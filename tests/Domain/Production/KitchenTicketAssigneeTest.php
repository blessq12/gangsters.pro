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
            new KitchenTicketLine(1, 'Ролл', 1),
        ]);
        $ticket->assignId(5);
        $ticket->start(77, 2);

        $this->assertSame(KitchenTicketStatus::Started, $ticket->status());
        $this->assertSame(77, $ticket->assigneeEmployeeId());
    }

    public function testЧужойНеМожетЗавершить(): void
    {
        $ticket = KitchenTicket::queue(10, 2, [
            new KitchenTicketLine(1, 'Ролл', 1),
        ]);
        $ticket->assignId(5);
        $ticket->start(77, 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Тикет закреплён за другим поваром.');
        $ticket->complete(99);
    }

    public function testAssigneeЗавершает(): void
    {
        $ticket = KitchenTicket::queue(10, 2, [
            new KitchenTicketLine(1, 'Ролл', 1),
        ]);
        $ticket->assignId(5);
        $ticket->start(77, 2);
        $ticket->complete(77);

        $this->assertSame(KitchenTicketStatus::Completed, $ticket->status());
    }
}
