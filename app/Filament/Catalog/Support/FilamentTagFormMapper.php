<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\CreateAdminTagDTO;
use App\Application\Catalog\DTO\UpdateAdminTagDTO;
use App\Infrastructure\Product\Model\PRD_Tag;

final class FilamentTagFormMapper
{
    public static function toFormState(PRD_Tag $tag): array
    {
        return [
            'label' => (string) $tag->label,
            'color' => (string) ($tag->color ?? 'amber'),
            'is_active' => (bool) $tag->is_active,
            'sort_order' => (int) $tag->sort_order,
            'code' => $tag->code,
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
