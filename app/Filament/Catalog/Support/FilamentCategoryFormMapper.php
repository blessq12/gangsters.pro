<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\CreateCategoryDTO;
use App\Application\Catalog\DTO\UpdateCategoryDTO;
use App\Infrastructure\Category\Model\PRD_Category;

final class FilamentCategoryFormMapper
{
    public static function toFormState(PRD_Category $category): array
    {
        return [
            'name' => (string) $category->name,
            'sort_order' => (int) $category->sort_order,
            'is_active' => (bool) $category->is_active,
            'slug' => $category->slug,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toCreateDto(array $data): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            name: (string) ($data['name'] ?? ''),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toUpdateDto(int $categoryId, array $data): UpdateCategoryDTO
    {
        return new UpdateCategoryDTO(
            categoryId: $categoryId,
            name: (string) ($data['name'] ?? ''),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
