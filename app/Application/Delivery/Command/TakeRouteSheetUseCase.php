<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\TakeRouteSheetDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Port\DeliveryCourierLookupPort;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Курьер берёт лист. Заказы в in_transit переводит слушатель RouteSheetTaken.
 */
final class TakeRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly DeliveryCourierLookupPort $couriers,
        private readonly RouteSheetPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(TakeRouteSheetDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $this->couriers->assertActiveCourier($dto->courierId);

            $sheet = $this->requireSheet($dto->sheetId);
            $sheet = $sheet->take($dto->courierId);
            $this->sheets->save($sheet);
            $this->events->publish($sheet);

            return $this->presenter->present($sheet);
        });
    }

    private function requireSheet(int $sheetId): RouteSheet
    {
        return $this->sheets->findById($sheetId)
            ?? throw new \InvalidArgumentException('Маршрутный лист не найден.');
    }
}
