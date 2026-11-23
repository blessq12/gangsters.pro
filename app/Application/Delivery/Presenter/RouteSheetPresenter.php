<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Entity\RouteStop;
use App\Domain\Core\Delivery\Routing\Port\DeliveryOrderStopSnapshotPort;

final class RouteSheetPresenter
{
    public function __construct(
        private readonly DeliveryOrderStopSnapshotPort $orderStops,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(RouteSheet $sheet, bool $hideCancelled = false): array
    {
        $orderIds = [];
        foreach ($sheet->stops() as $stop) {
            $orderIds[] = $stop->orderId();
        }
        $snapshots = $this->orderStops->findMany($orderIds);

        $stops = [];
        foreach ($sheet->stops() as $stop) {
            if ($hideCancelled && $stop->outcome() === RouteStop::OUTCOME_CANCELLED) {
                continue;
            }
            $stops[] = $this->presentStop($stop, $snapshots[$stop->orderId()] ?? null);
        }

        return [
            'id' => $sheet->hasId() ? $sheet->id() : null,
            'branch_id' => $sheet->branchId(),
            'status' => $sheet->status(),
            'courier_id' => $sheet->courierId(),
            'stops' => $stops,
        ];
    }

    /**
     * @param  list<RouteSheet>  $sheets
     * @return list<array<string, mixed>>
     */
    public function presentList(array $sheets, bool $hideCancelled = false): array
    {
        $result = [];
        foreach ($sheets as $sheet) {
            $result[] = $this->present($sheet, $hideCancelled);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>|null  $order
     * @return array<string, mixed>
     */
    private function presentStop(RouteStop $stop, ?array $order): array
    {
        return [
            'order_id' => $stop->orderId(),
            'position' => $stop->position(),
            'outcome' => $stop->outcome(),
            'reason' => $stop->reason() !== '' ? $stop->reason() : null,
            'order' => $order,
        ];
    }
}
