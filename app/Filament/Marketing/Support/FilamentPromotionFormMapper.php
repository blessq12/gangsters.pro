<?php

namespace App\Filament\Marketing\Support;

use App\Application\Marketing\Promotion\DTO\SavePromotionDTO;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;

final class FilamentPromotionFormMapper
{
    public static function toFormState(SYS_Promotion $promotion): array
    {
        return [
            'title' => (string) ($promotion->title ?? ''),
            'description' => $promotion->description,
            'image_upload' => self::uploadState($promotion->image),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toSaveDto(int $id, array $data, ?string $imagePath): SavePromotionDTO
    {
        return new SavePromotionDTO(
            id: $id,
            title: (string) ($data['title'] ?? ''),
            description: $data['description'] ?? null,
            image: $imagePath,
        );
    }

    /**
     * @return array<int, string>|null
     */
    private static function uploadState(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        return [$path];
    }
}
