<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\AdminTagDTO;
use App\Application\Catalog\DTO\CreateAdminTagDTO;
use App\Application\Catalog\DTO\UpdateAdminTagDTO;

final class FilamentTagFormMapper
{
    /**
     * @param  AdminTagDTO|array<string, mixed>  $tag
     * @return array<string, mixed>
     */
    public static function toFormState(AdminTagDTO|array $tag): array
    {
        if ($tag instanceof AdminTagDTO) {
            return [
                'label' => $tag->label,
                'color' => $tag->color,
                'is_active' => $tag->isActive,
                'sort_order' => $tag->sortOrder,
                'code' => $tag->code,
            ];
        }

        return [
            'label' => $tag['label'] ?? '',
            'color' => $tag['color'] ?? 'amber',
            'is_active' => (bool) ($tag['is_active'] ?? true),
            'sort_order' => (int) ($tag['sort_order'] ?? 0),
            'code' => $tag['code'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toCreateDto(array $data): CreateAdminTagDTO
    {
        return new CreateAdminTagDTO(
            label: (string) ($data['label'] ?? ''),
            color: (string) ($data['color'] ?? 'amber'),
            isActive: (bool) ($data['is_active'] ?? true),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toUpdateDto(int $tagId, array $data): UpdateAdminTagDTO
    {
        return new UpdateAdminTagDTO(
            id: $tagId,
            label: (string) ($data['label'] ?? ''),
            color: (string) ($data['color'] ?? 'amber'),
            isActive: (bool) ($data['is_active'] ?? true),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
