<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\CreateCategoryDTO;
use App\Application\Catalog\DTO\UpdateCategoryDTO;

final class FilamentCategoryFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        $category = $detail['category'] ?? $detail;

        return [
            'name' => $category['name'] ?? '',
            'sort_order' => (int) ($category['sort_order'] ?? 0),
            'is_active' => (bool) ($category['is_active'] ?? true),
            'slug' => $category['slug'] ?? null,
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
