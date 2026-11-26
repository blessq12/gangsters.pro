<?php

namespace App\Infrastructure\Production\Mapper;

use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Enum\KitchenTicketLineStatus;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;
use App\Infrastructure\Production\Model\KitchenTicketLineModel;
use App\Infrastructure\Production\Model\KitchenTicketModel;
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

            $doneBy = $lineRow->done_by !== null ? (int) $lineRow->done_by : null;
            if ($doneBy !== null && $doneBy < 1) {
                $doneBy = null;
            }
            $packedBy = $lineRow->packed_by !== null ? (int) $lineRow->packed_by : null;
            if ($packedBy !== null && $packedBy < 1) {
                $packedBy = null;
            }

            $statusValue = (string) ($lineRow->status ?? KitchenTicketLineStatus::Queued->value);
            $lineStatus = KitchenTicketLineStatus::tryFrom($statusValue) ?? KitchenTicketLineStatus::Queued;

            $lines[] = KitchenTicketLine::restore(
                id: (int) $lineRow->id,
                productId: (int) $lineRow->product_id,
                name: (string) $lineRow->name,
                quantity: (int) $lineRow->quantity,
                status: $lineStatus,
                doneBy: $doneBy,
                doneAt: $lineRow->done_at !== null
                    ? DateTimeImmutable::createFromInterface($lineRow->done_at)
                    : null,
                packedBy: $packedBy,
                packedAt: $lineRow->packed_at !== null
                    ? DateTimeImmutable::createFromInterface($lineRow->packed_at)
                    : null,
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
            $row = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'position' => $index,
                'status' => $line->status()->value,
                'done_by' => $line->doneBy(),
                'done_at' => $line->doneAt()?->format('Y-m-d H:i:s'),
                'packed_by' => $line->packedBy(),
                'packed_at' => $line->packedAt()?->format('Y-m-d H:i:s'),
            ];
            if ($line->hasId()) {
                $row['id'] = $line->id();
            }
            $lines[] = $row;
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
