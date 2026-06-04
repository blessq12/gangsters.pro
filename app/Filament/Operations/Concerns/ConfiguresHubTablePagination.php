<?php

namespace App\Filament\Operations\Concerns;

use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

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
}
