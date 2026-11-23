<?php

namespace App\Infrastructure\Production\Mapper;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use App\Infrastructure\Production\Model\KitchenTicketModel;
use App\Infrastructure\Production\Model\KitchenTicketLineModel;
use DateTimeImmutable;

final class KitchenTicketMapper
{
    public function toDomain(KitchenTicketModel $row): KitchenTicket
    {
        $status = KitchenTicketStatus::from((string) $row->status);

        $lines = [];
        $lineRows = $row->relationLoaded('lines')
            ? $row->lines
            : $row->lines()->get();

        foreach ($lineRows as $lineRow) {
            if (! $lineRow instanceof KitchenTicketLineModel) {
                continue;
            }

            $lines[] = new KitchenTicketLine(
                productId: (int) $lineRow->product_id,
                name: (string) $lineRow->name,
                quantity: (int) $lineRow->quantity,
            );
        }

        $assignee = $row->assignee_employee_id !== null ? (int) $row->assignee_employee_id : null;
        if ($assignee !== null && $assignee < 1) {
            $assignee = null;
        }

        return KitchenTicket::restore(
            id: (int) $row->id,
            orderId: (int) $row->order_id,
            branchId: (int) $row->branch_id,
            lines: $lines,
            status: $status,
            assigneeEmployeeId: $assignee,
            startedAt: $row->started_at !== null
                ? DateTimeImmutable::createFromInterface($row->started_at)
                : null,
            completedAt: $row->completed_at !== null
                ? DateTimeImmutable::createFromInterface($row->completed_at)
                : null,
        );
    }

    /**
     * @return array{ticket: array<string, mixed>, lines: list<array<string, mixed>>}
     */
    public function toPersistence(KitchenTicket $ticket): array
    {
        $lines = [];
        foreach (array_values($ticket->lines()) as $index => $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'position' => $index,
            ];
        }

        return [
            'ticket' => [
                'order_id' => $ticket->orderId(),
                'branch_id' => $ticket->branchId(),
                'assignee_employee_id' => $ticket->assigneeEmployeeId(),
                'status' => $ticket->status()->value,
                'started_at' => $ticket->startedAt()?->format('Y-m-d H:i:s'),
                'completed_at' => $ticket->completedAt()?->format('Y-m-d H:i:s'),
            ],
            'lines' => $lines,
        ];
    }
}
