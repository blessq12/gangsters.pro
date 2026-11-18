<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\TakeRouteSheetDto;
use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Repository\EmployeeRepository;

/**
 * Курьер берёт лист. Order переводит заказы в InTransit по RouteSheetTaken.
 */
final class TakeRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly EmployeeRepository $employees,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(TakeRouteSheetDto $dto): array
    {
        $courier = $this->employees->findById($dto->courierId);
        if ($courier === null) {
            throw new \InvalidArgumentException('Курьер не найден.');
        }
        if ($courier->role() !== StaffRole::Courier) {
            throw new \InvalidArgumentException('Сотрудник не является курьером.');
        }
        if (! $courier->isActive()) {
            throw new \InvalidArgumentException('Курьер деактивирован.');
        }

        $sheet = $this->sheets->findById($dto->sheetId);
        if ($sheet === null) {
            throw new \InvalidArgumentException('Маршрутный лист не найден.');
        }

        $sheet = $sheet->take($dto->courierId);
        $this->sheets->save($sheet);

        foreach ($sheet->pullDomainEvents() as $event) {
            event($event);
        }

        return $this->presenter->present($sheet);
    }
}
