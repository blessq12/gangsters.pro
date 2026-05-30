<?php

namespace App\Filament\Marketing\Support;

use App\Application\Marketing\Promotion\DTO\SavePromotionDTO;

final class FilamentPromotionFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return [
            'title' => $detail['title'] ?? '',
            'description' => $detail['description'] ?? null,
            'image_upload' => self::uploadState($detail['image'] ?? null),
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
