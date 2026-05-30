<?php

namespace App\Filament\Operations\Concerns;

use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;

trait ConfiguresHubTablePagination
{
    protected function configureHubPagination(Table $table, string $queryStringId): Table
    {
        return $table
            ->paginationMode(PaginationMode::Default)
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->queryStringIdentifier($queryStringId);
    }

    /**
     * @param  array{items: list<mixed>, total: int}  $result
     */
    protected function buildHubLengthAwarePaginator(
        array $result,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        return (new LengthAwarePaginator(
            collect($result['items'])->keyBy('id'),
            $result['total'],
            $perPage,
            max(1, $page),
            ['path' => request()->url(), 'pageName' => $this->getTablePaginationPageName()],
        ))->onEachSide(0);
    }

    protected function buildEmptyHubLengthAwarePaginator(int $perPage): LengthAwarePaginator
    {
        return (new LengthAwarePaginator([], 0, $perPage, 1))->onEachSide(0);
    }
}
