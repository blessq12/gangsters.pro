<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Infrastructure\Delivery\Mapper\RouteSheetMapper;
use App\Infrastructure\Delivery\Model\DLV_RouteSheet;
use App\Infrastructure\Delivery\Model\DLV_RouteStop;
use Illuminate\Support\Facades\DB;

final class EloquentRouteSheetRepository implements RouteSheetRepository
{
    public function __construct(
        private readonly RouteSheetMapper $mapper,
    ) {}

    public function findById(int $id): ?RouteSheet
    {
        $row = DLV_RouteSheet::query()->with('stops')->find($id);

        return $row instanceof DLV_RouteSheet ? $this->mapper->toDomain($row) : null;
    }

    public function findOpenByOrderId(int $orderId): ?RouteSheet
    {
        $stop = DLV_RouteStop::query()
            ->where('order_id', $orderId)
            ->where('status', 'pending')
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
            ->map(fn (DLV_RouteSheet $row): RouteSheet => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(RouteSheet $sheet): void
    {
        DB::transaction(function () use ($sheet): void {
            $payload = $this->mapper->toPersistence($sheet);

            if (! $sheet->hasId()) {
                $row = DLV_RouteSheet::query()->create($payload['sheet']);
                $sheet->assignId((int) $row->id);
            } else {
                DLV_RouteSheet::query()->whereKey($sheet->id())->update($payload['sheet']);
                DLV_RouteStop::query()->where('route_sheet_id', $sheet->id())->delete();
            }

            foreach ($payload['stops'] as $stop) {
                DLV_RouteStop::query()->create([
                    ...$stop,
                    'route_sheet_id' => $sheet->id(),
                ]);
            }
        });
    }
}
