<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Infrastructure\Delivery\Mapper\RouteSheetMapper;
use App\Infrastructure\Delivery\Model\DLV_RouteSheet;
use App\Infrastructure\Delivery\Model\DLV_RouteStop;
use Illuminate\Database\Eloquent\Builder;
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
        return $this->listActivePage(0, max(1, $this->countActive()));
    }

    public function listActivePage(int $offset, int $limit): array
    {
        return $this->mapPage($this->activeQuery(), $offset, $limit);
    }

    public function countActive(): int
    {
        return (int) $this->activeQuery()->count();
    }

    public function listActivePageByBranch(int $branchId, int $offset, int $limit): array
    {
        return $this->mapPage($this->activeQuery()->where('branch_id', $branchId), $offset, $limit);
    }

    public function countActiveByBranch(int $branchId): int
    {
        return (int) $this->activeQuery()->where('branch_id', $branchId)->count();
    }

    public function countOpen(?int $branchId = null): int
    {
        $query = $this->activeQuery();
        if ($branchId !== null && $branchId > 0) {
            $query->where('branch_id', $branchId);
        }

        return (int) $query->count();
    }

    public function listVisibleToCourierPage(int $courierId, int $offset, int $limit): array
    {
        return $this->mapPage($this->courierQuery($courierId), $offset, $limit);
    }

    public function countVisibleToCourier(int $courierId): int
    {
        return (int) $this->courierQuery($courierId)->count();
    }

    /**
     * @return Builder<DLV_RouteSheet>
     */
    private function activeQuery(): Builder
    {
        return DLV_RouteSheet::query()
            ->whereIn('status', ['released', 'in_delivery'])
            ->orderByDesc('id');
    }

    /**
     * @return Builder<DLV_RouteSheet>
     */
    private function courierQuery(int $courierId): Builder
    {
        return $this->activeQuery()->where('courier_id', $courierId);
    }

    /**
     * @param  Builder<DLV_RouteSheet>  $query
     * @return list<RouteSheet>
     */
    private function mapPage(Builder $query, int $offset, int $limit): array
    {
        return $query
            ->with('stops')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
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
