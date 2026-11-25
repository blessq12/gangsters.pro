<?php

namespace Tests\Domain\Production;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketLineStatus;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;
use App\Domain\Core\Production\Event\KitchenTicketLineDone;
use App\Domain\Core\Production\Event\KitchenTicketStarted;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use PHPUnit\Framework\TestCase;

final class KitchenTicketLineDoneTest extends TestCase
{
    public function testПоварОтмечаетЛиниюГотовой(): void
    {
        $ticket = $this->ticketWithLines([
            $this->line(101, 1, 'Ролл', 1),
            $this->line(102, 2, 'Суп', 1),
        ]);

        $ticket->markLineDone(101, 77, 2);

        $line = $ticket->lineById(101);
        $this->assertSame(KitchenTicketLineStatus::Done, $line->status());
        $this->assertSame(77, $line->doneBy());
        $this->assertSame(KitchenTicketStatus::Started, $ticket->status());

        $events = $ticket->pullDomainEvents();
        $this->assertInstanceOf(KitchenTicketStarted::class, $events[0]);
        $this->assertInstanceOf(KitchenTicketLineDone::class, $events[1]);
    }

    public function testДругойПоварМожетЗакрытьДругуюЛинию(): void
    {
        $ticket = $this->ticketWithLines([
            $this->line(101, 1, 'Ролл', 1),
            $this->line(102, 2, 'Суп', 1),
        ]);

        $ticket->markLineDone(101, 77, 2);
        $ticket->pullDomainEvents();
        $ticket->markLineDone(102, 88, 2);

        $this->assertSame(88, $ticket->lineById(102)->doneBy());
        $this->assertSame(KitchenTicketStatus::Completed, $ticket->status());

        $events = $ticket->pullDomainEvents();
        $this->assertInstanceOf(KitchenTicketLineDone::class, $events[0]);
        $this->assertInstanceOf(KitchenTicketCompleted::class, $events[1]);
    }

    public function testПовторныйDoneЗапрещён(): void
    {
        $ticket = $this->ticketWithLines([
            $this->line(101, 1, 'Ролл', 1),
            $this->line(102, 2, 'Суп', 1),
        ]);
        $ticket->markLineDone(101, 77, 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Позиция уже приготовлена.');
        $ticket->markLineDone(101, 88, 2);
    }

    public function testАдминПакуетЛинииПослеDone(): void
    {
        $ticket = $this->ticketWithLines([
            $this->line(101, 1, 'Ролл', 1),
            $this->line(102, 2, 'Суп', 1),
        ]);
        $ticket->markLineDone(101, 77, 2);
        $ticket->markLineDone(102, 77, 2);
        $ticket->pullDomainEvents();

        $ticket->markLinePacked(101, 5);
        $this->assertFalse($ticket->allActiveLinesPacked());

        $ticket->markLinePacked(102, 5);
        $this->assertTrue($ticket->allActiveLinesPacked());
        $this->assertSame(5, $ticket->lineById(102)->packedBy());
    }

    /**
     * @param  list<KitchenTicketLine>  $lines
     */
    private function ticketWithLines(array $lines): KitchenTicket
    {
        $ticket = KitchenTicket::queue(10, 2, $lines);
        $ticket->assignId(5);

        return $ticket;
    }

    private function line(int $id, int $productId, string $name, int $quantity): KitchenTicketLine
    {
        $line = KitchenTicketLine::snapshot($productId, $name, $quantity);
        $line->assignId($id);

        return $line;
    }
}
