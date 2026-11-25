<?php

namespace App\Infrastructure\Delivery\Repository;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Infrastructure\Delivery\Mapper\RouteSheetMapper;
use App\Infrastructure\Delivery\Model\RouteSheetModel;
use App\Infrastructure\Delivery\Model\RouteStopModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentRouteSheetRepository implements RouteSheetRepository
{
    public function __construct(
        private readonly RouteSheetMapper $mapper,
    ) {}

    public function findById(int $id): ?RouteSheet
    {
        $row = RouteSheetModel::query()->with('stops')->find($id);

        return $row instanceof RouteSheetModel ? $this->mapper->toDomain($row) : null;
    }

    public function findOpenByOrderId(int $orderId): ?RouteSheet
    {
        $stop = RouteStopModel::query()
            ->where('order_id', $orderId)
            ->where('status', 'pending')
            ->whereHas('sheet', function ($query): void {
                $query->whereIn('status', ['released', 'in_delivery']);
            })
            ->first();

        if (! $stop instanceof RouteStopModel) {
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

    public function listVisibleToCourierPage(
        int $courierId,
        int $offset,
        int $limit,
        ?string $status = null,
    ): array {
        return $this->mapPage($this->courierQuery($courierId, $status), $offset, $limit);
    }

    public function countVisibleToCourier(int $courierId, ?string $status = null): int
    {
        return (int) $this->courierQuery($courierId, $status)->count();
    }

    /**
     * @return Builder<RouteSheetModel>
     */
    private function activeQuery(): Builder
    {
        return RouteSheetModel::query()
            ->whereIn('status', ['released', 'in_delivery'])
            ->orderByDesc('id');
    }

    /**
     * @return Builder<RouteSheetModel>
     */
    private function courierQuery(int $courierId, ?string $status = null): Builder
    {
        $query = RouteSheetModel::query()
            ->where('courier_id', $courierId)
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            return $query->where('status', $status);
        }

        return $query->whereIn('status', ['released', 'in_delivery', 'completed']);
    }

    /**
     * @param  Builder<RouteSheetModel>  $query
     * @return list<RouteSheet>
     */
    private function mapPage(Builder $query, int $offset, int $limit): array
    {
        return $query
            ->with('stops')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (RouteSheetModel $row): RouteSheet => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(RouteSheet $sheet): void
    {
        DB::transaction(function () use ($sheet): void {
            $payload = $this->mapper->toPersistence($sheet);

            if (! $sheet->hasId()) {
                $row = RouteSheetModel::query()->create($payload['sheet']);
                $sheet->assignId((int) $row->id);
            } else {
                RouteSheetModel::query()->whereKey($sheet->id())->update($payload['sheet']);
                RouteStopModel::query()->where('route_sheet_id', $sheet->id())->delete();
            }

            foreach ($payload['stops'] as $stop) {
                RouteStopModel::query()->create([
                    ...$stop,
                    'route_sheet_id' => $sheet->id(),
                ]);
            }
        });
    }
}
