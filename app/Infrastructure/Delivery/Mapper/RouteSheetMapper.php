<?php

namespace App\Infrastructure\Delivery\Mapper;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Entity\RouteStop;
use App\Infrastructure\Delivery\Model\DLV_RouteSheet;
use App\Infrastructure\Delivery\Model\DLV_RouteStop;

final class RouteSheetMapper
{
    public function toDomain(DLV_RouteSheet $row): RouteSheet
    {
        $stops = [];
        $stopRows = $row->relationLoaded('stops')
            ? $row->stops
            : $row->stops()->get();

        foreach ($stopRows as $stop) {
            if (! $stop instanceof DLV_RouteStop) {
                continue;
            }

            $stops[] = new RouteStop(
                orderId: (int) $stop->order_id,
                position: (int) $stop->position,
                outcome: (string) $stop->status,
                reason: (string) ($stop->settlement_note ?? ''),
            );
        }

        return RouteSheet::restore(
            (int) $row->id,
            (int) ($row->branch_id ?? 0),
            (string) $row->status,
            $stops,
            $row->courier_id !== null ? (int) $row->courier_id : null,
        );
    }

    /**
     * @return array{sheet: array<string, mixed>, stops: list<array<string, mixed>>}
     */
    public function toPersistence(RouteSheet $sheet): array
    {
        $stops = [];
        foreach ($sheet->stops() as $stop) {
            $stops[] = [
                'order_id' => $stop->orderId(),
                'position' => $stop->position(),
                'status' => $stop->outcome(),
                'settlement_note' => $stop->reason() !== '' ? $stop->reason() : null,
            ];
        }

        return [
            'sheet' => [
                'branch_id' => $sheet->branchId(),
                'status' => $sheet->status(),
                'courier_id' => $sheet->courierId(),
            ],
            'stops' => $stops,
        ];
    }
}
