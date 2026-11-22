<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class DeleteCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $id): array
    {
        $this->categories->delete($id);

        return ['ok' => true];
    }
}
