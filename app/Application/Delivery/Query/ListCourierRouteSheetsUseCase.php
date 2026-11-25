<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class ListCourierRouteSheetsUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * Листы курьера во всех статусах (или один статус через $status).
     *
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        int $courierId,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
        ?string $status = null,
    ): array {
        if ($courierId < 1) {
            throw new \InvalidArgumentException('courierId должен быть положительным.');
        }

        $normalizedStatus = $status !== null && trim($status) !== '' ? trim($status) : null;
        if ($normalizedStatus !== null && ! in_array($normalizedStatus, ['released', 'in_delivery', 'completed'], true)) {
            throw new \InvalidArgumentException('Некорректный статус маршрутного листа.');
        }

        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList(
                $this->sheets->listVisibleToCourierPage($courierId, $offset, $limit, $normalizedStatus),
                hideCancelled: true,
            ),
            $this->sheets->countVisibleToCourier($courierId, $normalizedStatus),
            $offset,
            $limit,
        );
    }
}
