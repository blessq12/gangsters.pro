<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Entity\RouteStop;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;

final class RouteSheetPresenter
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(RouteSheet $sheet, bool $hideCancelled = false): array
    {
        $stops = [];
        foreach ($sheet->stops() as $stop) {
            if ($hideCancelled && $stop->outcome() === RouteStop::OUTCOME_CANCELLED) {
                continue;
            }
            $stops[] = $this->presentStop($stop);
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
     * @return array<string, mixed>
     */
    private function presentStop(RouteStop $stop): array
    {
        $order = $this->orders->findById($stop->orderId());

        return [
            'order_id' => $stop->orderId(),
            'position' => $stop->position(),
            'outcome' => $stop->outcome(),
            'reason' => $stop->reason() !== '' ? $stop->reason() : null,
            'order' => $order !== null ? $this->orderSnapshot($order) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSnapshot(Order $order): array
    {
        $delivery = $order->delivery();
        $client = $order->client();
        $address = $delivery->address();

        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'method' => $delivery->type()->value,
            'address' => $address === null ? null : [
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
            ],
            'client' => [
                'name' => $client->name(),
                'phone' => $client->phone(),
            ],
        ];
    }
}
