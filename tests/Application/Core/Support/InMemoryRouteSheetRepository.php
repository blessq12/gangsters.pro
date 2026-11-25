<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class InMemoryRouteSheetRepository implements RouteSheetRepository
{
    /** @var array<int, RouteSheet> */
    private array $sheets = [];

    private int $nextId = 1;

    public function findById(int $id): ?RouteSheet
    {
        return $this->sheets[$id] ?? null;
    }

    public function findOpenByOrderId(int $orderId): ?RouteSheet
    {
        foreach ($this->sheets as $sheet) {
            if (! in_array($sheet->status(), ['released', 'in_delivery'], true)) {
                continue;
            }

            foreach ($sheet->stops() as $stop) {
                if ($stop->orderId() === $orderId && $stop->outcome() === 'pending') {
                    return $sheet;
                }
            }
        }

        return null;
    }

    public function listActive(): array
    {
        return array_values(array_filter(
            $this->sheets,
            static fn (RouteSheet $sheet): bool => in_array($sheet->status(), ['released', 'in_delivery'], true),
        ));
    }

    public function listActivePage(int $offset, int $limit): array
    {
        return array_slice($this->listActive(), $offset, $limit);
    }

    public function countActive(): int
    {
        return count($this->listActive());
    }

    public function listActivePageByBranch(int $branchId, int $offset, int $limit): array
    {
        return array_slice($this->listActiveByBranch($branchId), $offset, $limit);
    }

    public function countActiveByBranch(int $branchId): int
    {
        return count($this->listActiveByBranch($branchId));
    }

    public function countOpen(?int $branchId = null): int
    {
        if ($branchId === null || $branchId < 1) {
            return $this->countActive();
        }

        return $this->countActiveByBranch($branchId);
    }

    /**
     * @return list<RouteSheet>
     */
    private function listActiveByBranch(int $branchId): array
    {
        return array_values(array_filter(
            $this->listActive(),
            static fn (RouteSheet $sheet): bool => $sheet->branchId() === $branchId,
        ));
    }

    public function listVisibleToCourierPage(
        int $courierId,
        int $offset,
        int $limit,
        ?string $status = null,
    ): array {
        return array_slice($this->visibleToCourier($courierId, $status), $offset, $limit);
    }

    public function countVisibleToCourier(int $courierId, ?string $status = null): int
    {
        return count($this->visibleToCourier($courierId, $status));
    }

    /**
     * @return list<RouteSheet>
     */
    private function visibleToCourier(int $courierId, ?string $status = null): array
    {
        $allowed = $status !== null && $status !== ''
            ? [$status]
            : ['released', 'in_delivery', 'completed'];

        return array_values(array_filter(
            array_values($this->sheets),
            static function (RouteSheet $sheet) use ($courierId, $allowed): bool {
                if ($sheet->courierId() !== $courierId) {
                    return false;
                }

                return in_array($sheet->status(), $allowed, true);
            },
        ));
    }

    public function save(RouteSheet $sheet): void
    {
        if (! $sheet->hasId()) {
            $sheet->assignId($this->nextId++);
        }

        $this->sheets[$sheet->id()] = $sheet;
    }
}
