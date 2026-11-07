<?php

namespace App\Application\Delivery\Query;

use App\Application\Order\Presenter\AdminOrderPresenter;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Entity\RouteStop;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Repository\OrderRepository;

final class ListActiveRouteSheetsUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly OrderRepository $orders,
        private readonly AdminOrderPresenter $orderPresenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return array_map(
            fn (RouteSheet $sheet): array => $this->present($sheet),
            $this->sheets->listActive(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function present(RouteSheet $sheet): array
    {
        return [
            'id' => $sheet->id(),
            'status' => $sheet->status(),
            'stops' => array_map(
                fn (RouteStop $stop): array => $this->stop($stop),
                $sheet->stops(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stop(RouteStop $stop): array
    {
        $order = $this->orders->findById($stop->orderId());
        $presented = $order !== null ? $this->orderPresenter->present($order) : null;
        $client = is_array($presented['client'] ?? null) ? $presented['client'] : [];
        $delivery = is_array($presented['delivery'] ?? null) ? $presented['delivery'] : [];
        $payment = is_array($presented['payment'] ?? null) ? $presented['payment'] : [];

        return [
            'order_id' => $stop->orderId(),
            'position' => $stop->position(),
            'outcome' => $stop->outcome(),
            'reason' => $stop->reason(),
            'client_name' => (string) ($client['name'] ?? ''),
            'phone' => (string) ($client['phone'] ?? ''),
            'address' => (string) ($delivery['address'] ?? ''),
            'total' => (int) ($presented['total'] ?? 0),
            'payment' => (string) ($payment['method'] ?? ''),
            'latitude' => isset($delivery['latitude']) && is_numeric($delivery['latitude']) ? (float) $delivery['latitude'] : null,
            'longitude' => isset($delivery['longitude']) && is_numeric($delivery['longitude']) ? (float) $delivery['longitude'] : null,
        ];
    }
}
