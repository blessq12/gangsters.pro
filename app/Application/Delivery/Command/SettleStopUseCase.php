<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SettleStopDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Repository\OrderRepository;

final class SettleStopUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly OrderRepository $orders,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SettleStopDto $dto): array
    {
        $sheet = $this->sheets->findById($dto->sheetId);
        if ($sheet === null) {
            throw new \InvalidArgumentException('Маршрутный лист не найден.');
        }

        $sheet = $sheet->settle(
            $dto->orderId,
            trim($dto->outcome),
            (string) ($dto->reason ?? ''),
        );

        if (trim($dto->outcome) === 'delivered') {
            $order = $this->orders->findById($dto->orderId);
            if ($order === null) {
                throw new \InvalidArgumentException('Заказ не найден.');
            }
            $order->markDelivered();
            $this->orders->save($order);
        }

        $allSettled = true;
        foreach ($sheet->stops() as $stop) {
            if ($stop->outcome() === 'pending') {
                $allSettled = false;
                break;
            }
        }

        if ($allSettled) {
            $sheet = $sheet->complete();
        }

        $this->sheets->save($sheet);

        return $this->presenter->present($sheet);
    }
}
