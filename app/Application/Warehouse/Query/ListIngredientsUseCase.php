<?php

namespace App\Application\Warehouse\Query;

use App\Application\Shared\PageParams;
use App\Application\Warehouse\Presenter\IngredientPresenter;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;

final class ListIngredientsUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly IngredientPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList($this->ingredients->listPage($offset, $limit)),
            $this->ingredients->countAll(),
            $offset,
            $limit,
        );
    }
}
