<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;

final class ListAdminSetsUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $items,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT, ?string $q = null): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);
        $query = trim((string) ($q ?? ''));
        $needle = $query !== '' ? $query : null;

        $items = [];
        foreach ($this->items->listSetsPage($offset, $limit, $needle) as $set) {
            $items[] = $this->presenter->adminSet($set);
        }

        return PageParams::wrap(
            $items,
            $this->items->countSets($needle),
            $offset,
            $limit,
        );
    }
}
