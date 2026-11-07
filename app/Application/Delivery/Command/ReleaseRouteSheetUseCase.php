<?php

namespace App\Application\Delivery\Command;

use App\Application\Order\Command\ChangeAdminOrderStatusUseCase;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReleaseRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly OrderRepository $orders,
        private readonly ChangeAdminOrderStatusUseCase $changeStatus,
    ) {}

    /**
     * @param  list<int>  $orderIds
     */
    public function execute(array $orderIds): RouteSheet
    {
        $ids = [];
        foreach ($orderIds as $orderId) {
            if (! in_array($orderId, $ids, true)) {
                $ids[] = $orderId;
            }
        }

        if ($ids === []) {
            throw new InvalidArgumentException('В маршрутном листе нет заказов.');
        }

        foreach ($ids as $orderId) {
            $order = $this->orders->findById($orderId);
            $method = is_array($order?->delivery()) ? (string) ($order->delivery()['method'] ?? '') : '';
            if ($order === null || $order->status() !== OrderStatus::Ready->value || $method !== 'courier') {
                throw new InvalidArgumentException('В лист можно добавить только готовый курьерский заказ.');
            }
            if ($this->sheets->findOpenByOrderId($orderId) !== null) {
                throw new InvalidArgumentException('Заказ уже стоит в другом маршрутном листе.');
            }
        }

        return DB::transaction(function () use ($ids): RouteSheet {
            $sheet = RouteSheet::release($ids);
            $this->sheets->save($sheet);
            foreach ($ids as $orderId) {
                $this->changeStatus->execute($orderId, OrderStatus::InTransit->value);
            }

            return $sheet;
        });
    }
}
