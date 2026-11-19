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
     * Свои активные листы курьера и свободные (released без курьера).
     *
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(
        int $courierId,
        int $offset = 0,
        int $limit = PageParams::DEFAULT_LIMIT,
    ): array {
        if ($courierId < 1) {
            throw new \InvalidArgumentException('courierId должен быть положительным.');
        }

        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList(
                $this->sheets->listVisibleToCourierPage($courierId, $offset, $limit),
                hideCancelled: true,
            ),
            $this->sheets->countVisibleToCourier($courierId),
            $offset,
            $limit,
        );
    }
}
