<?php

namespace App\Filament\Support;

use Illuminate\Pagination\LengthAwarePaginator;

final class AdminCartRuleProductsTableQuery
{
    public function __construct(
        private readonly AdminProductTableQuery $products,
    ) {
    }

    public function paginate(
        ?string $search,
        ?string $status,
        int $page,
        int $perPage,
        string $pageName,
        ?bool $countsAsRoll = null,
        ?bool $giftCandidate = null,
        ?bool $isComplementSet = null,
    ): LengthAwarePaginator {
        return $this->products->paginate(
            search: $search,
            status: $status,
            page: $page,
            perPage: $perPage,
            pageName: $pageName,
            countsAsRoll: $countsAsRoll,
            giftCandidate: $giftCandidate,
            isComplementSet: $isComplementSet,
        );
    }
}
