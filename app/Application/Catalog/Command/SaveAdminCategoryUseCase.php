<?php

namespace App\Application\Catalog\Command;

use App\Domain\Catalog\Repository\CategoryRepository;

final class SaveAdminCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(?int $id, string $name, bool $isActive, bool $isAccompanying): array
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название категории.');
        }

        $category = $this->categories->save($id, $name, $isActive, $isAccompanying);

        return [
            'id' => $category->id(),
            'name' => $category->name(),
            'is_active' => $category->isActive(),
            'is_accompanying' => $category->isAccompanying(),
        ];
    }
}
