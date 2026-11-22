<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\CreateRouteSheetDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Core\Delivery\Routing\Entity\RouteSheet;
use App\Domain\Core\Delivery\Routing\Port\DeliveryRouteOrderEligibilityPort;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;
use App\Shared\Transaction\TransactionManager;

/**
 * Выпуск листа. Кандидаты проверяются через eligibility-порт; Order-агрегат не грузится.
 */
final class CreateRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly DeliveryRouteOrderEligibilityPort $eligibility,
        private readonly RouteSheetPresenter $presenter,
        private readonly DomainEventPublisher $events,
        private readonly TransactionManager $transaction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateRouteSheetDto $dto): array
    {
        return $this->transaction->run(function () use ($dto): array {
            $orderIds = $this->orderIds($dto->orderIds);
            $candidates = $this->eligibility->resolve($orderIds);

            $branchId = null;
            foreach ($candidates as $candidate) {
                $orderId = (int) $candidate['order_id'];
                $candidateBranch = (int) $candidate['branch_id'];

                if ($branchId === null) {
                    $branchId = $candidateBranch;
                } elseif ($branchId !== $candidateBranch) {
                    throw new \InvalidArgumentException('Все заказы листа должны быть одного филиала.');
                }

                if (! ($candidate['assignable'] ?? false)) {
                    throw new \InvalidArgumentException(
                        sprintf('Заказ #%d должен быть готовым заказом с доставкой.', $orderId),
                    );
                }

                if ($this->sheets->findOpenByOrderId($orderId) !== null) {
                    throw new \InvalidArgumentException(
                        sprintf('Заказ #%d уже в открытом маршрутном листе.', $orderId),
                    );
                }
            }

            $sheet = RouteSheet::release((int) $branchId, $orderIds);
            $this->sheets->save($sheet);
            $this->events->publish($sheet);

            return $this->presenter->present($sheet);
        });
    }

    /**
     * @param  list<int>  $rawIds
     * @return list<int>
     */
    private function orderIds(array $rawIds): array
    {
        $orderIds = [];
        foreach ($rawIds as $rawId) {
            $orderId = (int) $rawId;
            if ($orderId < 1) {
                throw new \InvalidArgumentException('Некорректный order_id.');
            }
            $orderIds[] = $orderId;
        }

        $orderIds = array_values(array_unique($orderIds));
        if ($orderIds === []) {
            throw new \InvalidArgumentException('Нужен хотя бы один заказ.');
        }

        return $orderIds;
    }
}
