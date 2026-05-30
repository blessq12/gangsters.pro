<?php

namespace App\Application\Operations\CartRules\Query;

use App\Application\Catalog\Contracts\AdminProductReadRepository;
use App\Application\Catalog\Presenter\AdminProductPresenter;

final class GetAdminCartRuleProductsQuery
{
    public function __construct(
        private readonly AdminProductReadRepository $products,
        private readonly AdminProductPresenter $presenter,
    ) {}

    public function execute(
        ?string $search = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 25,
        ?bool $countsAsRoll = null,
        ?bool $giftCandidate = null,
        ?bool $isComplementSet = null,
    ): array {
        $result = $this->products->paginate(
            $search,
            $status,
            $page,
            $perPage,
            $countsAsRoll,
            $giftCandidate,
            $isComplementSet,
        );

        return [
            'items' => array_map(
                fn ($product) => $this->presenter->presentCartRuleListItem($product),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
