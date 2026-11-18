<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Entity\RouteStop;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;

final class RouteSheetPresenter
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(RouteSheet $sheet): array
    {
        $stops = [];
        foreach ($sheet->stops() as $stop) {
            $stops[] = $this->presentStop($stop);
        }

        return [
            'id' => $sheet->hasId() ? $sheet->id() : null,
            'status' => $sheet->status(),
            'courier_id' => $sheet->courierId(),
            'stops' => $stops,
        ];
    }

    /**
     * @param  list<RouteSheet>  $sheets
     * @return list<array<string, mixed>>
     */
    public function presentList(array $sheets): array
    {
        $result = [];
        foreach ($sheets as $sheet) {
            $result[] = $this->present($sheet);
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

        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'address' => $delivery['address'] ?? null,
            'client' => [
                'name' => $client['name'] ?? null,
                'phone' => $client['phone'] ?? null,
                'kind' => $client['kind'] ?? null,
            ],
        ];
    }
}
