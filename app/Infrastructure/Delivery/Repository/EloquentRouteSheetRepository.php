<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Entity\RouteStop;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Infrastructure\Delivery\Model\DLV_RouteSheet;
use App\Infrastructure\Delivery\Model\DLV_RouteStop;
use DateTimeImmutable;

final class EloquentRouteSheetRepository implements RouteSheetRepository
{
    public function findById(int $id): ?RouteSheet
    {
        $row = DLV_RouteSheet::query()->with('stops')->find($id);

        return $row instanceof DLV_RouteSheet ? $this->toDomain($row) : null;
    }

    public function findOpenByOrderId(int $orderId): ?RouteSheet
    {
        $stop = DLV_RouteStop::query()
            ->where('order_id', $orderId)
            ->where('outcome', 'pending')
            ->whereHas('sheet', function ($query): void {
                $query->whereIn('status', ['released', 'in_delivery']);
            })
            ->first();

        if (! $stop instanceof DLV_RouteStop) {
            return null;
        }

        return $this->findById((int) $stop->route_sheet_id);
    }

    public function listActive(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, DLV_RouteSheet> $rows */
        $rows = DLV_RouteSheet::query()
            ->with('stops')
            ->whereIn('status', ['released', 'in_delivery'])
            ->orderByDesc('id')
            ->get();

        return $rows
            ->map(fn (DLV_RouteSheet $row): RouteSheet => $this->toDomain($row))
            ->all();
    }

    public function save(RouteSheet $sheet): void
    {
        if (! $sheet->hasId()) {
            $row = DLV_RouteSheet::query()->create([
                'status' => $sheet->status(),
                'created_at' => new DateTimeImmutable(),
            ]);
            $sheet->assignId((int) $row->id);
        } else {
            DLV_RouteSheet::query()->whereKey($sheet->id())->update([
                'status' => $sheet->status(),
            ]);
            DLV_RouteStop::query()->where('route_sheet_id', $sheet->id())->delete();
        }

        foreach ($sheet->stops() as $stop) {
            DLV_RouteStop::query()->create([
                'route_sheet_id' => $sheet->id(),
                'order_id' => $stop->orderId(),
                'position' => $stop->position(),
                'outcome' => $stop->outcome(),
                'reason' => $stop->reason(),
            ]);
        }
    }

    private function toDomain(DLV_RouteSheet $row): RouteSheet
    {
        $stops = [];
        foreach ($row->stops as $stop) {
            $stops[] = new RouteStop(
                (int) $stop->order_id,
                (int) $stop->position,
                (string) $stop->outcome,
                (string) $stop->reason,
            );
        }

        return RouteSheet::restore((int) $row->id, (string) $row->status, $stops);
    }
}
