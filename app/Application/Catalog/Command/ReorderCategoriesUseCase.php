<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class ReorderCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /**
     * @param  list<int>  $orderedIds
     * @return array{ok: true}
     */
    public function execute(array $orderedIds): array
    {
        $ids = [];
        foreach ($orderedIds as $id) {
            $value = (int) $id;
            if ($value < 1) {
                throw new \InvalidArgumentException('Некорректный id категории.');
            }
            if (isset($ids[$value])) {
                throw new \InvalidArgumentException('Дубликат id категории в порядке.');
            }
            $ids[$value] = $value;
        }

        $this->categories->reorder(array_values($ids));

        return ['ok' => true];
    }
}
