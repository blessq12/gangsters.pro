<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class ReplaceCategoryItemsUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /**
     * @param  list<array{item_id: int, sort_order?: int}>  $items
     * @return array{ok: true}
     */
    public function execute(int $categoryId, array $items): array
    {
        if ($this->categories->findById($categoryId) === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        $this->categories->replaceItems($categoryId, $items);

        return ['ok' => true];
    }
}
